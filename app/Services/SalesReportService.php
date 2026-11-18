<?php

namespace App\Services;

use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    public function build(array $filters): array
    {
        $base = $this->query($filters);
        $summary = (clone $base)->selectRaw(
            "COALESCE(SUM(CASE WHEN invoices.status <> 'refunded' THEN invoice_items.line_total ELSE 0 END), 0) gross_sales, ".
            "COALESCE(SUM(CASE WHEN invoices.status = 'refunded' THEN invoice_items.line_total ELSE 0 END), 0) refunds, ".
            "COALESCE(SUM(CASE WHEN invoices.status <> 'refunded' AND invoice_items.unit_cost IS NOT NULL THEN invoice_items.quantity * invoice_items.unit_cost ELSE 0 END), 0) cost_of_goods, ".
            "COALESCE(SUM(CASE WHEN invoices.status <> 'refunded' AND (invoice_items.unit_cost IS NULL OR invoice_items.unit_cost = 0) THEN invoice_items.quantity ELSE 0 END), 0) missing_cost_quantity, ".
            "COALESCE(SUM(CASE WHEN invoices.status <> 'refunded' AND invoice_items.cost_estimated = 1 AND invoice_items.unit_cost > 0 THEN invoice_items.quantity ELSE 0 END), 0) estimated_cost_quantity, ".
            "COUNT(DISTINCT CASE WHEN invoices.status <> 'refunded' THEN invoices.id END) invoice_count, ".
            "COUNT(DISTINCT CASE WHEN invoices.status = 'refunded' THEN invoices.id END) refund_count"
        )->first();

        $grossCents = $this->cents((string) $summary->gross_sales);
        $refundCents = $this->cents((string) $summary->refunds);
        $costCents = $this->cents((string) $summary->cost_of_goods);
        $netCents = $grossCents - $refundCents;
        $count = (int) $summary->invoice_count;

        return [
            'filters' => $filters,
            'summary' => [
                'gross_sales' => $this->decimal($grossCents),
                'discounts' => null,
                'refunds' => $this->decimal($refundCents),
                'net_sales' => $this->decimal($netCents),
                'invoice_count' => $count,
                'refund_count' => (int) $summary->refund_count,
                'average_ticket' => $count > 0 ? $this->decimal($this->roundedDivision($netCents, $count)) : '0.00',
                'cost_of_goods' => $this->decimal($costCents),
                'gross_profit' => $this->decimal($netCents - $costCents),
                'missing_cost_quantity' => (int) $summary->missing_cost_quantity,
                'estimated_cost_quantity' => (int) $summary->estimated_cost_quantity,
            ],
            'trend' => $this->trend($base, $filters['group_by']),
            'products' => $this->products($base),
            'categories' => $this->categories($base),
            'cashiers' => $this->cashiers($base),
            'payment_statuses' => $this->paymentStatuses($base),
        ];
    }

    public function exportRows(array $filters)
    {
        return $this->query($filters)
            ->select([
                'invoices.invoice_date',
                'invoices.invoice_number',
                'invoices.customer',
                'invoices.status',
                'invoice_items.product_name',
                'invoice_items.quantity',
                'invoice_items.unit_price',
                'invoice_items.unit_cost',
                'invoice_items.cost_estimated',
                'invoice_items.line_total',
            ])
            ->orderBy('invoices.invoice_date')
            ->orderBy('invoices.id')
            ->cursor();
    }

    private function query(array $filters): Builder
    {
        return InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->leftJoin('products', 'products.id', '=', 'invoice_items.product_id')
            ->where('invoices.invoice_date', '>=', $filters['from'])
            ->where('invoices.invoice_date', '<', Carbon::parse($filters['to'])->addDay()->toDateString())
            ->when(isset($filters['branch_id']), fn (Builder $q) => $q->where('invoices.branch_id', $filters['branch_id']))
            ->when(isset($filters['cashier_id']), fn (Builder $q) => $q->where('invoices.cashier_id', $filters['cashier_id']))
            ->when(isset($filters['category_id']), fn (Builder $q) => $q->where('products.category_id', $filters['category_id']))
            ->when(isset($filters['product_id']), fn (Builder $q) => $q->where('invoice_items.product_id', $filters['product_id']))
            ->when(isset($filters['payment_status']), function (Builder $q) use ($filters): void {
                match ($filters['payment_status']) {
                    'paid' => $q->whereColumn('invoices.paid_amount', '>=', 'invoices.total_amount'),
                    'unpaid' => $q->where('invoices.paid_amount', '=', 0),
                    'partial' => $q->where('invoices.paid_amount', '>', 0)->whereColumn('invoices.paid_amount', '<', 'invoices.total_amount'),
                    default => null,
                };
            });
    }

    private function trend(Builder $base, string $groupBy): array
    {
        $driver = DB::connection()->getDriverName();
        $expression = match ($groupBy) {
            'week' => $driver === 'sqlite' ? "strftime('%Y-%W', invoices.invoice_date)" : "DATE_FORMAT(invoices.invoice_date, '%x-%v')",
            'month' => $driver === 'sqlite' ? "strftime('%Y-%m', invoices.invoice_date)" : "DATE_FORMAT(invoices.invoice_date, '%Y-%m')",
            default => 'DATE(invoices.invoice_date)',
        };

        return (clone $base)
            ->selectRaw("{$expression} as period")
            ->selectRaw("COALESCE(SUM(CASE WHEN invoices.status <> 'refunded' THEN invoice_items.line_total ELSE -invoice_items.line_total END), 0) as net_sales")
            ->selectRaw("COUNT(DISTINCT CASE WHEN invoices.status <> 'refunded' THEN invoices.id END) as invoice_count")
            ->groupBy('period')
            ->orderBy('period')
            ->limit(120)
            ->get()->map(fn ($row) => [
                'period' => (string) $row->period,
                'net_sales' => $this->decimal($this->cents((string) $row->net_sales)),
                'invoice_count' => (int) $row->invoice_count,
            ])->all();
    }

    private function products(Builder $base): array
    {
        $query = (clone $base)->selectRaw(
            'invoice_items.product_id, invoice_items.product_name as name, '.
            'SUM(CASE WHEN invoices.status <> \'refunded\' THEN invoice_items.quantity ELSE -invoice_items.quantity END) as net_quantity, '.
            'SUM(CASE WHEN invoices.status <> \'refunded\' THEN invoice_items.line_total ELSE -invoice_items.line_total END) as net_sales'
        )->groupBy('invoice_items.product_id', 'invoice_items.product_name');

        $map = fn ($row) => [
            'product_id' => $row->product_id ? (int) $row->product_id : null,
            'name' => $row->name,
            'quantity' => (int) $row->net_quantity,
            'net_sales' => $this->decimal($this->cents((string) $row->net_sales)),
        ];

        return [
            'top' => (clone $query)->orderByDesc('net_quantity')->limit(10)->get()->map($map)->all(),
            'bottom' => (clone $query)->orderBy('net_quantity')->limit(10)->get()->map($map)->all(),
        ];
    }

    private function categories(Builder $base): array
    {
        return (clone $base)->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw("COALESCE(categories.name, 'غير مصنف') as name")
            ->selectRaw("SUM(CASE WHEN invoices.status <> 'refunded' THEN invoice_items.line_total ELSE -invoice_items.line_total END) as net_sales")
            ->selectRaw('SUM(CASE WHEN invoices.status <> \'refunded\' THEN invoice_items.quantity ELSE -invoice_items.quantity END) as net_quantity')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('net_sales')->limit(50)->get()->map(fn ($row) => [
                'name' => $row->name,
                'quantity' => (int) $row->net_quantity,
                'net_sales' => $this->decimal($this->cents((string) $row->net_sales)),
            ])->all();
    }

    private function cashiers(Builder $base): array
    {
        return (clone $base)->leftJoin('users', 'users.id', '=', 'invoices.cashier_id')
            ->selectRaw("COALESCE(users.name, 'غير محدد') as name")
            ->selectRaw("SUM(CASE WHEN invoices.status <> 'refunded' THEN invoice_items.line_total ELSE -invoice_items.line_total END) as net_sales")
            ->selectRaw('COUNT(DISTINCT invoices.id) as invoice_count')
            ->groupBy('invoices.cashier_id', 'users.name')
            ->orderByDesc('net_sales')->limit(50)->get()->map(fn ($row) => [
                'name' => $row->name,
                'invoice_count' => (int) $row->invoice_count,
                'net_sales' => $this->decimal($this->cents((string) $row->net_sales)),
            ])->all();
    }

    private function paymentStatuses(Builder $base): array
    {
        return (clone $base)->selectRaw("CASE WHEN invoices.paid_amount = 0 THEN 'unpaid' WHEN invoices.paid_amount >= invoices.total_amount THEN 'paid' ELSE 'partial' END as status")
            ->selectRaw('COUNT(DISTINCT invoices.id) as invoice_count')
            ->groupBy('status')->orderBy('status')->get()->map(fn ($row) => [
                'status' => $row->status,
                'invoice_count' => (int) $row->invoice_count,
            ])->all();
    }

    private function decimal(int $cents): string
    {
        $absolute = abs($cents);
        $formatted = intdiv($absolute, 100).'.'.str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);

        return $cents < 0 ? '-'.$formatted : $formatted;
    }

    private function cents(string $decimal): int
    {
        $decimal = trim($decimal);
        if (! preg_match('/^(-?)(\d+)(?:\.(\d+))?$/', $decimal, $matches)) {
            return 0;
        }
        $fraction = str_pad($matches[3] ?? '', 3, '0');
        $cents = ((int) $matches[2] * 100) + (int) substr($fraction, 0, 2);
        if ((int) $fraction[2] >= 5) {
            $cents++;
        }

        return ($matches[1] ?? '') === '-' ? -$cents : $cents;
    }

    private function roundedDivision(int $amount, int $divisor): int
    {
        $absolute = abs($amount);
        $result = intdiv($absolute, $divisor);
        if (($absolute % $divisor) * 2 >= $divisor) {
            $result++;
        }

        return $amount < 0 ? -$result : $result;
    }
}
