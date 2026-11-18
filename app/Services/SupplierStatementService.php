<?php

namespace App\Services;

use App\Models\Supplier;
use App\Support\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupplierStatementService
{
    /** @return array{aging:array<string,int>,opening_balance:int,recorded_balance:int,unallocated_difference:int,purchases:LengthAwarePaginator,payments:LengthAwarePaginator} */
    public function statement(Supplier $supplier, array $filters = []): array
    {
        $purchaseQuery = $supplier->purchases()
            ->when(($filters['status'] ?? 'all') === 'open', fn ($query) => $query->where('remaining', '>', 0))
            ->when(($filters['status'] ?? 'all') === 'paid', fn ($query) => $query->where('remaining', '=', 0))
            ->when(isset($filters['from']), fn ($query) => $query->whereDate('purchase_date', '>=', $filters['from']))
            ->when(isset($filters['to']), fn ($query) => $query->whereDate('purchase_date', '<=', $filters['to']));
        $paymentQuery = $supplier->payments()->with('purchase:id,invoice_number')
            ->when(isset($filters['from']), fn ($query) => $query->whereDate('payment_date', '>=', $filters['from']))
            ->when(isset($filters['to']), fn ($query) => $query->whereDate('payment_date', '<=', $filters['to']));

        $aging = ['current' => 0, 'days_1_30' => 0, 'days_31_60' => 0, 'days_61_90' => 0, 'over_90_days' => 0];
        foreach ($supplier->purchases()->where('remaining', '>', 0)->get(['remaining', 'purchase_date']) as $purchase) {
            $age = $purchase->purchase_date->copy()->startOfDay()->diffInDays(today());
            $bucket = match (true) {
                $age <= 0 => 'current',
                $age <= 30 => 'days_1_30',
                $age <= 60 => 'days_31_60',
                $age <= 90 => 'days_61_90',
                default => 'over_90_days',
            };
            $aging[$bucket] += Money::cents($purchase->remaining);
        }

        $opening = Money::cents($supplier->opening_balance);
        $balance = Money::cents($supplier->balance);
        $agedInvoices = array_sum($aging);

        return [
            'aging' => $aging,
            'opening_balance' => $opening,
            'recorded_balance' => $balance,
            'unallocated_difference' => $balance - $opening - $agedInvoices,
            'purchases' => $purchaseQuery->latest('purchase_date')->latest('id')->paginate(min(100, max(1, (int) ($filters['per_page'] ?? 20))), ['*'], 'purchases_page')->withQueryString(),
            'payments' => $paymentQuery->latest('payment_date')->latest('id')->paginate(min(100, max(1, (int) ($filters['per_page'] ?? 20))), ['*'], 'payments_page')->withQueryString(),
        ];
    }
}
