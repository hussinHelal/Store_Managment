<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletCashAdjustment;
use App\Models\WalletExpense;
use App\Models\WalletTransaction;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Daily and monthly wallet reports.
 * Reversed transactions and reversal rows never count. Net profit = commission - provider fees - expenses.
 */
class WalletReportController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'mode' => ['nullable', Rule::in(['daily', 'monthly'])],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        $mode = $request->query('mode') === 'monthly' ? 'monthly' : 'daily';

        if ($mode === 'daily') {
            $anchor = Carbon::createFromFormat('Y-m-d', (string) $request->query('date', now()->toDateString()))->startOfDay();
            $start = $anchor->copy()->startOfDay();
            $end = $anchor->copy()->endOfDay();
        } else {
            $anchor = Carbon::createFromFormat('Y-m-d', ((string) $request->query('month', now()->format('Y-m'))).'-01')->startOfDay();
            $start = $anchor->copy()->startOfMonth();
            $end = $anchor->copy()->endOfMonth();
        }

        $from = $start->format('Y-m-d H:i:s');
        $to = $end->format('Y-m-d H:i:s');

        $movements = fn () => DB::table('wallet_transactions')
            ->whereIn('type', ['send', 'receive'])
            ->where('status', 'completed')
            ->whereBetween('occurred_at', [$from, $to]);

        $sums = "COUNT(*) AS txns,
                 COALESCE(SUM(CASE WHEN wallet_transactions.type = 'send' THEN wallet_transactions.amount END), 0) AS sent,
                 COALESCE(SUM(CASE WHEN wallet_transactions.type = 'receive' THEN wallet_transactions.amount END), 0) AS received,
                 COALESCE(SUM(wallet_transactions.commission), 0) AS commission,
                 COALESCE(SUM(wallet_transactions.fee), 0) AS fee,
                 COALESCE(SUM(wallet_transactions.profit), 0) AS profit";

        $perWallet = $movements()
            ->join('wallets', 'wallets.id', '=', 'wallet_transactions.wallet_id')
            ->groupBy('wallets.id', 'wallets.name', 'wallets.provider')
            ->orderBy('wallets.name')
            ->selectRaw("wallets.name, wallets.provider, {$sums}")
            ->get()
            ->map(fn ($row) => $this->toCents($row, [
                'name' => $row->name,
                'provider' => Wallet::PROVIDERS[$row->provider] ?? $row->provider,
            ]))
            ->all();

        $totals = ['txns' => 0, 'sent' => 0, 'received' => 0, 'commission' => 0, 'fee' => 0, 'profit' => 0];
        foreach ($perWallet as $row) {
            foreach ($totals as $key => $value) {
                $totals[$key] += $row[$key];
            }
        }

        $expenseQuery = fn () => WalletExpense::query()->whereNull('voided_at')
            ->whereBetween('spent_on', [$start->toDateString(), $end->toDateString()]);

        $expensesTotal = Money::cents($expenseQuery()->sum('amount'));
        $expenseList = $mode === 'daily' ? $expenseQuery()->orderBy('id')->get() : collect();

        // Cash drawer movement in the period.
        $cashFromTransactions = Money::cents(DB::table('wallet_transactions')->whereBetween('occurred_at', [$from, $to])->sum('cash_delta'));
        $cashAdjustments = Money::cents(WalletCashAdjustment::query()->whereBetween('occurred_at', [$from, $to])->sum('amount'));
        $collections = Money::cents(DB::table('wallet_transactions')->where('type', 'settlement')->whereBetween('occurred_at', [$from, $to])->sum('cash_delta'));
        $newDeferred = Money::cents(
            DB::table('wallet_transactions')->where('payment_method', 'deferred')->where('type', 'send')->where('status', 'completed')
                ->whereBetween('occurred_at', [$from, $to])->selectRaw('COALESCE(SUM(amount + commission), 0) AS total')->value('total')
        );

        $days = [];
        if ($mode === 'monthly') {
            $byDay = $movements()
                ->groupBy(DB::raw('DATE(wallet_transactions.occurred_at)'))
                ->orderBy(DB::raw('DATE(wallet_transactions.occurred_at)'))
                ->selectRaw("DATE(wallet_transactions.occurred_at) AS day, {$sums}")
                ->get()->keyBy('day');

            $expenseByDay = $expenseQuery()
                ->selectRaw('spent_on AS day, SUM(amount) AS total')
                ->groupBy('spent_on')->get()
                ->mapWithKeys(fn ($row) => [Carbon::parse($row->getRawOriginal('spent_on') ?? $row->day)->format('Y-m-d') => Money::cents($row->total)]);

            $dates = collect($byDay->keys())->merge($expenseByDay->keys())->unique()->sort()->values();

            foreach ($dates as $date) {
                $row = $byDay->get($date);
                $base = $row ? $this->toCents($row, ['day' => $date]) : ['day' => $date, 'txns' => 0, 'sent' => 0, 'received' => 0, 'commission' => 0, 'fee' => 0, 'profit' => 0];
                $base['expenses'] = $expenseByDay->get($date, 0);
                $base['net'] = $base['profit'] - $base['expenses'];
                $days[] = $base;
            }
        }

        return view('wallets.reports', [
            'mode' => $mode,
            'date' => $start->toDateString(),
            'month' => $start->format('Y-m'),
            'perWallet' => $perWallet,
            'totals' => $totals,
            'expensesTotal' => $expensesTotal,
            'net' => $totals['profit'] - $expensesTotal,
            'expenseList' => $expenseList,
            'days' => $days,
            'cash' => [
                'movement' => $cashFromTransactions + $cashAdjustments - $expensesTotal,
                'collections' => $collections,
                'new_deferred' => $newDeferred,
            ],
        ]);
    }

    /** @return array<string,int|string> */
    private function toCents(object $row, array $extra): array
    {
        return $extra + [
            'txns' => (int) $row->txns,
            'sent' => Money::cents($row->sent),
            'received' => Money::cents($row->received),
            'commission' => Money::cents($row->commission),
            'fee' => Money::cents($row->fee),
            'profit' => Money::cents($row->profit),
        ];
    }
}
