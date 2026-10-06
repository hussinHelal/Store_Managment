<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\invoice;
use App\Models\products;
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
                $priceCents = (int) round((float) $product->price * 100);
                $lineTotalCents = $priceCents * $quantity;

                $items[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => number_format($priceCents / 100, 2, '.', ''),
                    'quantity' => $quantity,
                    'line_total' => number_format($lineTotalCents / 100, 2, '.', ''),
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

            $paidCents = (int) round((float) $data['paid_amount'] * 100);
            if ($paidCents > $totalCents) {
                throw ValidationException::withMessages(['paid_amount' => 'لا يمكن أن يتجاوز المبلغ المدفوع إجمالي الفاتورة.']);
            }

            do {
                $invoiceNumber = 'INV-'.now()->format('YmdHis').'-'.Str::upper(Str::random(8));
            } while (invoice::query()->where('invoice_number', $invoiceNumber)->exists());

            $firstProduct = $lockedProducts->get($data['product_ids'][0]);
            $totalAmount = number_format($totalCents / 100, 2, '.', '');
            $paidAmount = number_format($paidCents / 100, 2, '.', '');

            $invoice = invoice::create([
                'invoice_number' => $invoiceNumber,
                'customer' => $data['customer'],
                'product_id' => $firstProduct->id,
                'quantity' => $totalQuantity,
                'invoice_date' => $data['invoice_date'],
                'total_amount' => $totalAmount,
                'product_price' => $firstProduct->price,
                'paid_amount' => $paidAmount,
                'status' => $paidCents >= $totalCents ? 'paid' : 'unpaid',
                'items' => $items,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($quantitiesByProduct as $productId => $quantity) {
                $product = $lockedProducts->get($productId);
                $product->stock -= $quantity;
                $product->total_sold += $quantity;
                $product->save();
            }

            $syncInstallment($invoice, $items, $totalCents / 100, $paidCents / 100);

            AppNotification::create([
                'title' => 'تم إنشاء فاتورة',
                'message' => 'تم إنشاء الفاتورة #'.$invoice->invoice_number,
                'is_active' => true,
                'created_by' => $userId,
            ]);

            return $invoice;
        }, 3);
    }
}
