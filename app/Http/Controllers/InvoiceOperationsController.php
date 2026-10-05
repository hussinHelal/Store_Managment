<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\installments;
use App\Models\invoice;
use App\Models\products;
use App\Services\InvoiceCreationService;
use App\Services\InvoiceInstallmentSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceOperationsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['search' => ['nullable', 'string', 'max:80']]);

        $invoices = invoice::with('product')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->toString().'%';
                $query->where(fn ($query) => $query
                    ->where('customer', 'like', $search)
                    ->orWhere('invoice_number', 'like', $search));
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('invoice.index', compact('invoices'));
    }

    public function create()
    {
        $products = products::query()->orderBy('name')->get();
        $allProducts = $products->map(fn (products $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'barcode' => $product->barcode,
        ])->all();

        return view('invoice.create', compact('products', 'allProducts'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedInvoiceInput($request);
        $invoice = app(InvoiceCreationService::class)->create(
            $validated,
            $request->user()->id,
            fn (invoice $invoice, array $items, float $total, float $paid) =>
                $this->syncInvoiceInstallment($invoice, $items, $total, $paid)
        );

        return redirect()->route('invoices.index')->with('success', 'تم إنشاء الفاتورة بنجاح.');
    }

    public function edit(invoice $invoice)
    {
        abort_if($invoice->status === 'refunded', 404);
        $products = products::query()->orderBy('name')->get();
        $allProducts = $products->map(fn (products $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'barcode' => $product->barcode,
        ])->all();

        return view('invoice.edit', compact('invoice', 'products', 'allProducts'));
    }

    public function show(invoice $invoice)
    {
        $invoice->load('product');

        return view('invoice.show', compact('invoice'));
    }

    public function update(Request $request, invoice $invoice)
    {
        $validated = $this->validatedInvoiceInput($request);
        $userId = $request->user()->id;

        DB::transaction(function () use ($invoice, $validated, $userId): void {
            $lockedInvoice = invoice::query()->lockForUpdate()->findOrFail($invoice->id);
            abort_if($lockedInvoice->status === 'refunded', 404);

            $oldQuantities = $this->itemQuantities($lockedInvoice);
            $newQuantities = [];
            $productIds = array_values(array_unique(array_merge(
                array_keys($oldQuantities),
                $validated['product_ids']
            )));
            $lockedProducts = products::query()->whereIn('id', $productIds)
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            if ($lockedProducts->count() !== count($productIds)) {
                throw ValidationException::withMessages(['product_ids' => 'أحد منتجات الفاتورة لم يعد متاحاً.']);
            }

            $items = [];
            $totalCents = 0;
            $totalQuantity = 0;

            foreach ($validated['product_ids'] as $index => $productId) {
                $quantity = (int) $validated['quantities'][$index];
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
                $newQuantities[$product->id] = ($newQuantities[$product->id] ?? 0) + $quantity;
                $totalCents += $lineTotalCents;
                $totalQuantity += $quantity;
            }

            $paidCents = (int) round((float) $validated['paid_amount'] * 100);
            if ($paidCents > $totalCents) {
                throw ValidationException::withMessages(['paid_amount' => 'لا يمكن أن يتجاوز المبلغ المدفوع إجمالي الفاتورة.']);
            }

            foreach ($productIds as $productId) {
                $product = $lockedProducts->get($productId);
                $stock = $product->stock + ($oldQuantities[$productId] ?? 0) - ($newQuantities[$productId] ?? 0);

                if ($stock < 0) {
                    throw ValidationException::withMessages([
                        'product_ids' => 'المخزون غير كافٍ للمنتج '.$product->name.'.',
                    ]);
                }

                $product->stock = $stock;
                $product->total_sold = max(0, $product->total_sold - ($oldQuantities[$productId] ?? 0) + ($newQuantities[$productId] ?? 0));
                $product->save();
            }

            $firstProduct = $lockedProducts->get($validated['product_ids'][0]);
            $total = number_format($totalCents / 100, 2, '.', '');
            $paid = number_format($paidCents / 100, 2, '.', '');

            $lockedInvoice->update([
                'customer' => $validated['customer'],
                'product_id' => $firstProduct->id,
                'quantity' => $totalQuantity,
                'invoice_date' => $validated['invoice_date'],
                'total_amount' => $total,
                'product_price' => $firstProduct->price,
                'paid_amount' => $paid,
                'status' => $paidCents >= $totalCents ? 'paid' : 'unpaid',
                'items' => $items,
                'notes' => $validated['notes'] ?? $lockedInvoice->notes,
            ]);

            $this->syncInvoiceInstallment($lockedInvoice, $items, $totalCents / 100, $paidCents / 100);
            AppNotification::create([
                'title' => 'تم تحديث فاتورة',
                'message' => 'تم تحديث الفاتورة #'.$lockedInvoice->invoice_number,
                'is_active' => true,
                'created_by' => $userId,
            ]);
        }, 3);

        return redirect()->route('invoices.index')->with('success', 'تم تحديث الفاتورة بنجاح.');
    }

    public function destroy(invoice $invoice)
    {
        $userId = request()->user()->id;

        try {
            DB::transaction(function () use ($invoice, $userId): void {
                $lockedInvoice = invoice::query()->lockForUpdate()->findOrFail($invoice->id);

                if (installments::query()->where('invoice_id', $lockedInvoice->id)->exists()) {
                    throw ValidationException::withMessages(['invoice' => 'لا يمكن حذف فاتورة لها سجل أقساط. استخدم استرداد الفاتورة بدلاً من ذلك.']);
                }

                if ($lockedInvoice->status !== 'refunded') {
                    $quantities = $this->itemQuantities($lockedInvoice);
                    $products = products::query()->whereIn('id', array_keys($quantities))
                        ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                    foreach ($quantities as $productId => $quantity) {
                        $product = $products->get($productId);
                        if ($product) {
                            $product->stock += $quantity;
                            $product->total_sold = max(0, $product->total_sold - $quantity);
                            $product->save();
                        }
                    }
                }

                AppNotification::create([
                    'title' => 'تم حذف فاتورة',
                    'message' => 'تم حذف الفاتورة #'.$lockedInvoice->invoice_number,
                    'is_active' => true,
                    'created_by' => $userId,
                ]);
                $lockedInvoice->delete();
            }, 3);
        } catch (ValidationException $exception) {
            return redirect()->route('invoices.index')->with('error', $exception->errors()['invoice'][0]);
        }

        return redirect()->route('invoices.index')->with('success', 'تم حذف الفاتورة بنجاح.');
    }

    public function refund(invoice $invoice)
    {
        $userId = request()->user()->id;

        try {
            DB::transaction(function () use ($invoice, $userId): void {
                $lockedInvoice = invoice::query()->lockForUpdate()->findOrFail($invoice->id);
                if ($lockedInvoice->status === 'refunded') {
                    throw ValidationException::withMessages(['invoice' => 'تم استرداد هذه الفاتورة من قبل.']);
                }

                $quantities = $this->itemQuantities($lockedInvoice);
                $products = products::query()->whereIn('id', array_keys($quantities))
                    ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                foreach ($quantities as $productId => $quantity) {
                    $product = $products->get($productId);
                    if ($product) {
                        $product->stock += $quantity;
                        $product->total_sold = max(0, $product->total_sold - $quantity);
                        $product->save();
                    }
                }

                $lockedInvoice->update(['status' => 'refunded']);
                installments::query()->where('invoice_id', $lockedInvoice->id)->update([
                    'remaining' => 0,
                    'status' => 'مسترد',
                    'next_payment_date' => null,
                ]);
                AppNotification::create([
                    'title' => 'تم استرجاع فاتورة',
                    'message' => 'تم استرجاع الفاتورة #'.$lockedInvoice->invoice_number,
                    'is_active' => true,
                    'created_by' => $userId,
                ]);
            }, 3);
        } catch (ValidationException $exception) {
            return redirect()->route('invoices.index')->with('error', $exception->errors()['invoice'][0]);
        }

        return redirect()->route('invoices.index')->with('success', 'تم استرداد الفاتورة بنجاح.');
    }

    public function print(invoice $invoice)
    {
        $invoice->load('product');

        return view('invoice.print', compact('invoice'));
    }

    private function validatedInvoiceInput(Request $request): array
    {
        if ($request->filled('barcode')) {
            $request->validate(['barcode' => ['string', 'max:80']]);
            $product = products::where('barcode', trim($request->input('barcode')))->first();
            if (!$product) {
                throw ValidationException::withMessages(['barcode' => 'لا يوجد منتج مطابق لهذا الباركود.']);
            }

            $productIds = $request->input('product_ids', []);
            $quantities = $request->input('quantities', []);
            $productIds = is_array($productIds) ? $productIds : [];
            $quantities = is_array($quantities) ? $quantities : [];
            $productIds[] = $product->id;
            $quantities[] = 1;
            $request->merge(['product_ids' => $productIds, 'quantities' => $quantities]);
        }

        $productIds = $request->input('product_ids', []);
        $quantityCount = is_array($productIds) ? count($productIds) : 0;

        return $request->validate([
            'customer' => ['required', 'string', 'max:255'],
            'product_ids' => ['required', 'array', 'min:1', 'max:100'],
            'product_ids.*' => ['required', 'integer'],
            'quantities' => ['required', 'array', 'size:'.$quantityCount, 'max:100'],
            'quantities.*' => ['required', 'integer', 'min:1', 'max:100000'],
            'invoice_date' => ['required', 'date'],
            'paid_amount' => ['required', 'numeric', 'decimal:0,2', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], ['notes.max' => 'يجب ألا تتجاوز الملاحظات 2000 حرف.']);
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

    private function syncInvoiceInstallment(invoice $invoice, array $items, float $totalAmount, float $paidAmount): void
    {
        app(InvoiceInstallmentSync::class)->sync($invoice, $items, $totalAmount, $paidAmount);
    }
}
