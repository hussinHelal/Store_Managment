<?php

namespace App\Services;

use App\Models\installments;
use App\Models\invoice;

/**
 * Creates or updates the installment (debt) row that belongs to an invoice.
 *
 * This is the same logic as InvoiceOperationsController::syncInvoiceInstallment(),
 * extracted so the cashier can reuse it. The invoice controller is left untouched
 * on purpose; it can be pointed at this class later with a one-line change.
 *
 * Must run inside the transaction opened by InvoiceCreationService.
 */
class InvoiceInstallmentSync
{
    public function sync(invoice $invoice, array $items, float $totalAmount, float $paidAmount): void
    {
        $remainingCents = max(0, (int) round($totalAmount * 100) - (int) round($paidAmount * 100));

        $installment = installments::query()
            ->where('invoice_id', $invoice->id)
            ->lockForUpdate()
            ->first();

        $data = [
            'invoice_id' => $invoice->id,
            'customer' => $invoice->customer,
            'product_id' => $invoice->product_id,
            'product_name' => collect($items)->pluck('name')->implode(', '),
            'product_price' => number_format($totalAmount, 2, '.', ''),
            'quantity' => $invoice->quantity,
            'paid_amount' => number_format($paidAmount, 2, '.', ''),
            'remaining' => number_format($remainingCents / 100, 2, '.', ''),
            'status' => $remainingCents === 0 ? 'مكتمل' : 'غير مكتمل',
            'items' => $items,
            'next_payment_date' => $remainingCents > 0 ? now()->addMonth()->toDateString() : null,
        ];

        if ($installment) {
            $installment->update($data);
        } elseif ($remainingCents > 0) {
            installments::create($data);
        }
    }
}
