<?php

namespace App\Services;

use App\Models\products;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseReceivingService
{
    public function __construct(private readonly StockService $stock, private readonly CurrentBranch $currentBranch) {}

    /** @param array{branch_id:int,purchase_date:string,payment_type:string,paid_amount?:string|int|null,notes?:?string,items:list<array{product_id:int,quantity:int,unit_cost:string|int}>} $data */
    public function receive(Supplier $supplier, array $data, User $actor, string $key): Purchase
    {
        return DB::transaction(function () use ($supplier, $data, $actor, $key): Purchase {
            $normalized = $data;
            usort($normalized['items'], fn ($a, $b) => [(int) ($a['product_id'] ?? 0), (string) ($a['product_name'] ?? ''), (int) $a['quantity'], (string) $a['unit_cost']] <=> [(int) ($b['product_id'] ?? 0), (string) ($b['product_name'] ?? ''), (int) $b['quantity'], (string) $b['unit_cost']]);
            $payloadHash = hash('sha256', json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            $existing = Purchase::query()->where('idempotency_key', $key)->lockForUpdate()->first();
            if ($existing) {
                if (! hash_equals((string) $existing->payload_hash, $payloadHash)) {
                    throw ValidationException::withMessages(['idempotency_key' => 'مفتاح العملية مستخدم لبيانات فاتورة أخرى.']);
                }
                if ((int) $existing->supplier_id !== (int) $supplier->id) {
                    throw ValidationException::withMessages(['idempotency_key' => 'مفتاح العملية مستخدم لمورد آخر.']);
                }

                return $existing->load(['items.product', 'branch', 'payments']);
            }

            $branchId = (int) ($data['branch_id'] ?? $this->currentBranch->id($actor));
            $branch = DB::table('branches')->where('id', $branchId)->where('is_active', true)->lockForUpdate()->first();
            if (! $branch) {
                throw ValidationException::withMessages(['branch_id' => 'الفرع المحدد غير متاح للاستلام.']);
            }

            $supplier = Supplier::query()->lockForUpdate()->findOrFail($supplier->id);
            $productIds = collect($data['items'])->pluck('product_id')->filter()->map(fn ($id) => (int) $id)->unique()->sort()->values();
            $productsById = products::query()->whereIn('id', $productIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            if ($productsById->count() !== $productIds->count()) {
                throw ValidationException::withMessages(['items' => 'أحد المنتجات لم يعد موجوداً.']);
            }

            $totalCents = 0;
            foreach ($data['items'] as $line) {
                $totalCents += Money::cents($line['unit_cost']) * (int) $line['quantity'];
            }
            if ($totalCents <= 0 || $totalCents > 999_999_999_999) {
                throw ValidationException::withMessages(['items' => 'إجمالي الفاتورة خارج الحد المسموح.']);
            }

            $paidCents = $data['payment_type'] === 'cash' ? $totalCents : Money::cents($data['paid_amount'] ?? 0);
            if ($paidCents > $totalCents || ($data['payment_type'] === 'cash' && $paidCents !== $totalCents)) {
                throw ValidationException::withMessages(['paid_amount' => 'تحقق من المبلغ المدفوع ونوع الدفع.']);
            }
            $remainingCents = $totalCents - $paidCents;
            $balanceCents = Money::cents($supplier->balance);
            if ($balanceCents + $remainingCents > 999_999_999_999) {
                throw ValidationException::withMessages(['items' => 'تجاوز رصيد المورد الحد المالي المسموح.']);
            }

            $purchase = Purchase::query()->create([
                'supplier_id' => $supplier->id,
                'branch_id' => (int) $branch->id,
                'invoice_number' => $this->purchaseNumber(),
                'idempotency_key' => $key,
                'payload_hash' => $payloadHash,
                'purchase_date' => $data['purchase_date'],
                'payment_type' => $data['payment_type'],
                'total_amount' => Money::decimal($totalCents),
                'paid_amount' => Money::decimal($paidCents),
                'remaining' => Money::decimal($remainingCents),
                'status' => Purchase::statusFromAmounts($paidCents, $totalCents),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $index => $line) {
                $product = ! empty($line['product_id']) ? $productsById->get((int) $line['product_id']) : null;
                $quantity = (int) $line['quantity'];
                $unitCost = Money::cents($line['unit_cost']);
                $purchaseItem = PurchaseItem::query()->create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product?->id,
                    'product_name' => $product?->name ?? trim($line['product_name']),
                    'quantity' => $quantity,
                    'received_quantity' => $quantity,
                    'unit_cost' => Money::decimal($unitCost),
                    'line_total' => Money::decimal($unitCost * $quantity),
                ]);
                if ($product) {
                    $movementKey = hash('sha256', $key.':line:'.$index);
                    $this->stock->increase(
                        (int) $product->id,
                        $quantity,
                        'purchase',
                        $actor,
                        'استلام فاتورة المورد '.$purchase->invoice_number,
                        $purchaseItem,
                        $movementKey,
                        (int) $branch->id,
                    );
                }
            }

            if ($remainingCents > 0) {
                $supplier->forceFill(['balance' => Money::decimal($balanceCents + $remainingCents)])->save();
            }
            if ($paidCents > 0) {
                SupplierPayment::query()->create([
                    'supplier_id' => $supplier->id,
                    'purchase_id' => $purchase->id,
                    'receipt_number' => $this->receiptNumber(),
                    'amount' => Money::decimal($paidCents),
                    'payment_date' => $data['purchase_date'],
                    'notes' => 'سداد فاتورة شراء '.$purchase->invoice_number,
                ]);
            }

            return $purchase->load(['items.product', 'branch', 'payments']);
        }, 3);
    }

    private function purchaseNumber(): string
    {
        do {
            $number = 'PUR-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Purchase::query()->where('invoice_number', $number)->exists());

        return $number;
    }

    private function receiptNumber(): string
    {
        do {
            $number = 'SP-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (SupplierPayment::query()->where('receipt_number', $number)->exists());

        return $number;
    }
}
