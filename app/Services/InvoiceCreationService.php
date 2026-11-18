<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\installments;
use App\Models\invoice;
use App\Models\products;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceCreationService
{
    public function create(array $data, int $userId, callable $syncInstallment): invoice
    {
        return DB::transaction(function () use ($data, $userId, $syncInstallment): invoice {
            $productIds = array_values(array_unique($data['product_ids']));
            $lockedProducts = products::query()->whereIn('id', $productIds)
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            if ($lockedProducts->count() !== count($productIds)) {
                throw ValidationException::withMessages(['product_ids' => 'أحد المنتجات المحددة لم يعد متاحاً.']);
            }

            $items = [];
            $quantitiesByProduct = [];
            $totalCents = 0;
            $totalQuantity = 0;

            foreach ($data['product_ids'] as $index => $productId) {
                $quantity = (int) $data['quantities'][$index];
                $product = $lockedProducts->get($productId);
                $priceCents = Money::cents($product->price);
                $lineTotalCents = $priceCents * $quantity;

                $items[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => Money::decimal($priceCents),
                    'quantity' => $quantity,
                    'line_total' => Money::decimal($lineTotalCents),
                ];

                $quantitiesByProduct[$product->id] = ($quantitiesByProduct[$product->id] ?? 0) + $quantity;
                $totalCents += $lineTotalCents;
                $totalQuantity += $quantity;
            }

            foreach ($quantitiesByProduct as $productId => $quantity) {
                if ($lockedProducts->get($productId)->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'product_ids' => 'المخزون غير كافٍ للمنتج '.$lockedProducts->get($productId)->name.'.',
                    ]);
                }
            }

            $paidCents = Money::cents($data['paid_amount']);
            if ($paidCents > $totalCents) {
                throw ValidationException::withMessages(['paid_amount' => 'لا يمكن أن يتجاوز المبلغ المدفوع إجمالي الفاتورة.']);
            }

            do {
                $invoiceNumber = 'INV-'.now()->format('YmdHis').'-'.Str::upper(Str::random(8));
            } while (invoice::query()->where('invoice_number', $invoiceNumber)->exists());

            $firstProduct = $lockedProducts->get($data['product_ids'][0]);
            $totalAmount = Money::decimal($totalCents);
            $paidAmount = Money::decimal($paidCents);

            $invoice = invoice::create([
                'invoice_number' => $invoiceNumber,
                'customer' => $data['customer'],
                'product_id' => $firstProduct->id,
                'cashier_id' => $userId,
                'branch_id' => app(CurrentBranch::class)->id(User::query()->find($userId)),
                'quantity' => $totalQuantity,
                'invoice_date' => $data['invoice_date'],
                'total_amount' => $totalAmount,
                'product_price' => $firstProduct->price,
                'paid_amount' => $paidAmount,
                'status' => $paidCents >= $totalCents ? 'paid' : 'unpaid',
                'items' => $items,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->storeCostSnapshots($invoice, $items, $lockedProducts);

            foreach ($quantitiesByProduct as $productId => $quantity) {
                $product = $lockedProducts->get($productId);
                app(StockService::class)->decrease(
                    (int) $productId,
                    $quantity,
                    'sale',
                    User::query()->find($userId),
                    'بيع ضمن الفاتورة '.$invoice->invoice_number,
                    $invoice,
                );
                $product->total_sold += $quantity;
                $product->save();
            }

            $syncInstallment($invoice, $items, Money::decimal($totalCents), Money::decimal($paidCents));

            AppNotification::create([
                'title' => 'تم إنشاء فاتورة',
                'message' => 'تم إنشاء الفاتورة #'.$invoice->invoice_number,
                'is_active' => true,
                'created_by' => $userId,
            ]);

            return $invoice;
        }, 3);
    }

    public function update(invoice $invoice, array $data, int $userId, callable $syncInstallment): invoice
    {
        return DB::transaction(function () use ($invoice, $data, $userId, $syncInstallment): invoice {
            $lockedInvoice = invoice::query()->lockForUpdate()->findOrFail($invoice->id);
            if ($lockedInvoice->status === 'refunded') {
                throw ValidationException::withMessages(['invoice' => 'لا يمكن تعديل فاتورة مستردة.']);
            }

            $oldQuantities = $this->itemQuantities($lockedInvoice);
            $newQuantities = [];
            $productIds = array_values(array_unique(array_merge(
                array_keys($oldQuantities),
                $data['product_ids']
            )));
            $lockedProducts = products::query()->whereIn('id', $productIds)
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            if ($lockedProducts->count() !== count($productIds)) {
                throw ValidationException::withMessages(['product_ids' => 'أحد منتجات الفاتورة لم يعد متاحاً.']);
            }

            $items = [];
            $totalCents = 0;
            $totalQuantity = 0;

            foreach ($data['product_ids'] as $index => $productId) {
                $quantity = (int) $data['quantities'][$index];
                $product = $lockedProducts->get($productId);
                $priceCents = Money::cents($product->price);
                $lineTotalCents = $priceCents * $quantity;

                $items[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => Money::decimal($priceCents),
                    'quantity' => $quantity,
                    'line_total' => Money::decimal($lineTotalCents),
                ];
                $newQuantities[$product->id] = ($newQuantities[$product->id] ?? 0) + $quantity;
                $totalCents += $lineTotalCents;
                $totalQuantity += $quantity;
            }

            $paidCents = Money::cents($data['paid_amount']);
            if ($paidCents > $totalCents) {
                throw ValidationException::withMessages(['paid_amount' => 'لا يمكن أن يتجاوز المبلغ المدفوع إجمالي الفاتورة.']);
            }

            foreach ($productIds as $productId) {
                $product = $lockedProducts->get($productId);
                $stockDelta = ($oldQuantities[$productId] ?? 0) - ($newQuantities[$productId] ?? 0);
                $stock = $product->stock + $stockDelta;
                if ($stock < 0) {
                    throw ValidationException::withMessages([
                        'product_ids' => 'المخزون غير كافٍ للمنتج '.$product->name.'.',
                    ]);
                }
                if ($stockDelta !== 0) {
                    app(StockService::class)->change(
                        (int) $productId,
                        $stockDelta,
                        'sale_edit',
                        User::query()->find($userId),
                        'تعديل أصناف الفاتورة '.$lockedInvoice->invoice_number,
                        $lockedInvoice,
                    );
                }
                $product->total_sold = max(0, $product->total_sold - ($oldQuantities[$productId] ?? 0) + ($newQuantities[$productId] ?? 0));
                $product->save();
            }

            $firstProduct = $lockedProducts->get($data['product_ids'][0]);
            $total = Money::decimal($totalCents);
            $paid = Money::decimal($paidCents);
            $lockedInvoice->update([
                'customer' => $data['customer'],
                'product_id' => $firstProduct->id,
                'quantity' => $totalQuantity,
                'invoice_date' => $data['invoice_date'],
                'total_amount' => $total,
                'product_price' => $firstProduct->price,
                'paid_amount' => $paid,
                'status' => $paidCents >= $totalCents ? 'paid' : 'unpaid',
                'items' => $items,
                'notes' => $data['notes'] ?? $lockedInvoice->notes,
            ]);

            $this->storeCostSnapshots($lockedInvoice, $items, $lockedProducts);

            $syncInstallment($lockedInvoice, $items, Money::decimal($totalCents), Money::decimal($paidCents));
            AppNotification::create([
                'title' => 'تم تحديث فاتورة',
                'message' => 'تم تحديث الفاتورة #'.$lockedInvoice->invoice_number,
                'is_active' => true,
                'created_by' => $userId,
            ]);

            return $lockedInvoice->refresh();
        }, 3);
    }

    private function storeCostSnapshots(invoice $invoice, array $items, $products): void
    {
        $invoice->lines()->delete();
        foreach ($items as $item) {
            $product = $products->get((int) $item['product_id']);
            $cost = $product?->cost_price;
            $invoice->lines()->create([
                'product_id' => $product?->id,
                'product_name' => $item['name'],
                'quantity' => (int) $item['quantity'],
                'unit_price' => $item['price'],
                'unit_cost' => $cost,
                'cost_estimated' => false,
                'line_total' => $item['line_total'],
            ]);
        }
    }

    /** @return array<int, int> */
    /**
     * Refund an invoice: the sold units go back to stock, the invoice is marked
     * refunded and any installment attached to it is closed.
     */
    public function refund(invoice $invoice, int $userId): invoice
    {
        return DB::transaction(function () use ($invoice, $userId): invoice {
            $locked = invoice::query()->lockForUpdate()->findOrFail($invoice->id);
            if ($locked->status === 'refunded') {
                throw ValidationException::withMessages(['invoice' => 'تم استرداد هذه الفاتورة من قبل.']);
            }

            $quantities = $this->itemQuantities($locked);
            $products = products::query()->whereIn('id', array_keys($quantities))
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);
                if ($product) {
                    app(StockService::class)->increase(
                        (int) $productId,
                        $quantity,
                        'refund',
                        User::query()->find($userId),
                        'استرداد الفاتورة '.$locked->invoice_number,
                        $locked,
                    );
                    $product->total_sold = max(0, $product->total_sold - $quantity);
                    $product->save();
                }
            }

            $locked->update(['status' => 'refunded']);
            installments::query()->where('invoice_id', $locked->id)->update([
                'remaining' => 0,
                'status' => 'مسترد',
                'next_payment_date' => null,
            ]);
            AppNotification::query()->create([
                'title' => 'تم استرجاع فاتورة',
                'message' => 'تم استرجاع الفاتورة #'.$locked->invoice_number,
                'is_active' => true,
                'created_by' => $userId,
            ]);

            return $locked->refresh();
        }, 3);
    }

    private function itemQuantities(invoice $invoice): array
    {
        $items = is_array($invoice->items) && $invoice->items !== []
            ? $invoice->items
            : [['product_id' => $invoice->product_id, 'quantity' => $invoice->quantity]];
        $quantities = [];
        foreach ($items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            if ($productId > 0 && $quantity > 0) {
                $quantities[$productId] = ($quantities[$productId] ?? 0) + $quantity;
            }
        }

        return $quantities;
    }
}
