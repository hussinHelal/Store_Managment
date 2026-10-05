<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWalletTransactionRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\Wallets\WalletLedgerService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletCashierController extends Controller
{
    public function index(Request $request, WalletLedgerService $ledger)
    {
        $wallets = Wallet::query()->where('is_active', true)->orderBy('name')->get();

        // Everything the browser needs for the live limit preview, in cents.
        $walletData = $wallets->map(function (Wallet $wallet) use ($ledger): array {
            return [
                'id' => $wallet->id,
                'name' => $wallet->name,
                'provider' => $wallet->providerLabel(),
                'identifier' => $wallet->identifier,
                'balance' => Money::cents($wallet->balance),
                'perTransaction' => $wallet->limitCents('per_transaction_limit'),
                'limits' => [
                    'sent_day' => $wallet->limitCents('daily_send_limit'),
                    'sent_month' => $wallet->limitCents('monthly_send_limit'),
                    'received_day' => $wallet->limitCents('daily_receive_limit'),
                    'received_month' => $wallet->limitCents('monthly_receive_limit'),
                ],
                'usage' => $ledger->usageFor($wallet),
                'warn' => (int) $wallet->warn_at_percent,
                'commission' => [
                    'percent' => (float) $wallet->default_commission_percent,
                    'min' => Money::cents($wallet->default_commission_min),
                ],
                'fee' => [
                    'percent' => (float) $wallet->default_fee_percent,
                    'min' => Money::cents($wallet->default_fee_min),
                    'max' => $wallet->default_fee_max !== null ? Money::cents($wallet->default_fee_max) : null,
                ],
            ];
        })->values();

        $selectedWallet = (int) $request->query('wallet', $walletData->first()['id'] ?? 0);
        $selectedType = $request->query('type') === 'receive' ? 'receive' : 'send';

        $recent = WalletTransaction::query()
            ->with('wallet:id,name,provider')
            ->whereIn('type', ['send', 'receive'])
            ->latest('occurred_at')->latest('id')
            ->paginate(25, ['*'], 'transactions_page')
            ->withQueryString();

        $outstanding = Money::cents(
            WalletTransaction::query()->where('status', 'completed')->where('receivable', '>', 0)->sum('receivable')
        );

        return view('wallet_cashier.index', compact('walletData', 'selectedWallet', 'selectedType', 'recent', 'outstanding'));
    }

    /** Every deferred transfer that is still not fully collected, grouped by customer. */
    public function debts()
    {
        $customerExpression = "COALESCE(NULLIF(customer_name, ''), 'بدون اسم')";
        $customerPages = $this->outstandingDeferredTransactions()
            ->selectRaw($customerExpression.' as customer_group')
            ->groupByRaw($customerExpression)
            ->orderByRaw($customerExpression)
            ->paginate(20, ['customer_group'], 'customers_page')
            ->withQueryString();

        $customerNames = $customerPages->getCollection()->pluck('customer_group');
        $rowsByCustomer = $this->outstandingDeferredTransactions()
            ->with('wallet:id,name')
            ->whereIn(DB::raw($customerExpression), $customerNames->all())
            ->orderBy('occurred_at')
            ->get()
            ->groupBy(fn (WalletTransaction $transaction) => $transaction->customer_name ?: 'بدون اسم');

        $groups = $customerNames->mapWithKeys(fn (string $name) => [
            $name => $rowsByCustomer->get($name, collect()),
        ]);
        $total = Money::cents($this->outstandingDeferredTransactions()->sum('receivable'));

        return view('wallet_cashier.debts', compact('groups', 'customerPages', 'total'));
    }

    public function store(StoreWalletTransactionRequest $request, WalletLedgerService $ledger): RedirectResponse
    {
        $transaction = $ledger->record($request->validated(), $request->user(), $request->ip());

        return redirect()
            ->route('wallet_cashier.index', ['wallet' => $transaction->wallet_id, 'type' => $transaction->type])
            ->with('success', 'تم تسجيل العملية بنجاح.');
    }

    public function reverse(Request $request, WalletTransaction $transaction, WalletLedgerService $ledger): RedirectResponse
    {
        // Undoing a transaction is a manager action, separate from operating the cashier.
        abort_unless($request->user()->can('page.wallets.manage'), 403);

        $data = $request->validate(
            ['reason' => ['required', 'string', 'min:3', 'max:255']],
            [],
            ['reason' => 'سبب العكس']
        );

        $ledger->reverse($transaction, $data['reason'], $request->user(), $request->ip());

        return back()->with('success', 'تم عكس العملية وتسجيل قيد عكسي. لم يُحذف أي سجل.');
    }

    public function settle(Request $request, WalletTransaction $transaction, WalletLedgerService $ledger): RedirectResponse
    {
        $data = $request->validate(
            ['amount' => ['nullable', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999']],
            [],
            ['amount' => 'مبلغ التحصيل']
        );

        $ledger->settle($transaction, $data['amount'] ?? null, $request->user(), $request->ip());

        return back()->with('success', 'تم تسجيل التحصيل.');
    }

    private function outstandingDeferredTransactions()
    {
        return WalletTransaction::query()
            ->where('status', 'completed')
            ->where('payment_method', 'deferred')
            ->where('receivable', '>', 0);
    }
}
