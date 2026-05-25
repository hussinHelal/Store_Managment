<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\invoice;
use App\Models\installments;
use App\Models\products;
use App\Models\AppNotification;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::with('product')->orderBy('id', 'desc')->paginate(15);
        return view('invoice.index', ['invoices' => $invoices]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = products::all();
        return view('invoice.create', ['products' => $products]);
    }

    /**
     * Store a newly created resource in storage.
     */
     public function store(Request $request)
     {
         if ($request->user()->cannot('create-invoice', Invoice::class)) {
             return redirect()->route('invoices.index')
                 ->with('error', 'ليس لديك صلاحية إنشاء فاتورة.');
         }

        // Optional: allow adding a product by barcode (single scan). If barcode provided,
        // find the product and merge into product_ids/quantities for validation.
        if ($request->filled('barcode')) {
            $barcode = trim($request->input('barcode'));
            $foundProduct = products::where('barcode', $barcode)->first();
            if ($foundProduct) {
                $productIds = $request->input('product_ids', []);
                $quantities = $request->input('quantities', []);
                $productIds[] = $foundProduct->id;
                $quantities[] = 1;
                $request->merge(['product_ids' => $productIds, 'quantities' => $quantities]);
            } else {
                return redirect()->back()->withInput()->with('error', 'الباركود غير موجود.');
            }
        }

         $validated = $request->validate([
             'customer'      => 'required|string',
             'product_ids'   => 'required|array|min:1',
             'product_ids.*' => 'required|exists:products,id',
             'quantities'    => 'required|array|min:1',
             'quantities.*'  => 'required|integer|min:1',
             'invoice_date'  => 'required|date',
             'paid_amount'   => 'required|numeric|min:0',
         ]);

         $items = [];
         $totalAmount = 0;
         $totalQuantity = 0;

         foreach ($validated['product_ids'] as $index => $productId) {
             $quantity = intval($validated['quantities'][$index] ?? 1);
             $product = products::findOrFail($productId);
             $lineTotal = $product->price * $quantity;

             $items[] = [
                 'product_id' => $product->id,
                 'name'       => $product->name,
                 'price'      => $product->price,
                 'quantity'   => $quantity,
                 'line_total' => $lineTotal,
             ];

             $totalAmount += $lineTotal;
             $totalQuantity += $quantity;
         }

         $firstProduct = products::findOrFail($validated['product_ids'][0]);

         $invoice = invoice::create([
             'invoice_number' => 'INV-' . time(),
             'customer'       => $validated['customer'],
             'product_id'     => $firstProduct->id,
             'quantity'       => $totalQuantity,
             'invoice_date'   => $validated['invoice_date'],
             'total_amount'   => $totalAmount,
             'product_price'  => $firstProduct->price,
             'paid_amount'    => $validated['paid_amount'],
             'status'         => $validated['paid_amount'] >= $totalAmount ? 'paid' : 'unpaid',
             'items'          => $items,
         ]);

         foreach ($items as $item) {
             $product = products::find($item['product_id']);
             if ($product) {
                 $product->decrement('stock', $item['quantity']);
                 $product->increment('total_sold', $item['quantity']);
             }
         }

         $this->syncInvoiceInstallment($invoice, $items, $totalAmount, floatval($validated['paid_amount']));

        // create a notification for invoice creation
        try {
            AppNotification::create([
                'title' => 'تم إنشاء فاتورة',
                'message' => 'تم إنشاء الفاتورة #' . $invoice->invoice_number,
                'is_active' => true,
                'created_by' => $request->user()->id,
            ]);
        } catch (\Exception $e) {
            // ignore notification errors
        }

        return redirect()->route('invoices.index')->with('success', 'تم إنشاء الفاتورة بنجاح.');
     }

    /**
     * Display the specified resource.
     */
    // public function show(invoice $invoice)
    // {
    //     return view('invoice.show', ['invoice' => $invoice]);
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(invoice $invoice)
    {
        $products = products::all();
        return view('invoice.edit', ['invoice' => $invoice, 'products' => $products]);
    }

    /**
     * Update the specified resource in storage.
     */
     public function update(Request $request, invoice $invoice)
     {
         if ($request->user()->cannot('update-invoice', $invoice)) {
             return redirect()->route('invoices.index')
                 ->with('error', 'ليس لديك صلاحية تعديل هذه الفاتورة.');
         }

        // Optional: allow adding a product by barcode when updating an invoice.
        if ($request->filled('barcode')) {
            $barcode = trim($request->input('barcode'));
            $foundProduct = products::where('barcode', $barcode)->first();
            if ($foundProduct) {
                $productIds = $request->input('product_ids', []);
                $quantities = $request->input('quantities', []);
                $productIds[] = $foundProduct->id;
                $quantities[] = 1;
                $request->merge(['product_ids' => $productIds, 'quantities' => $quantities]);
            } else {
                return redirect()->back()->withInput()->with('error', 'الباركود غير موجود.');
            }
        }

         $validated = $request->validate([
             'customer'      => 'required|string',
             'product_ids'   => 'required|array|min:1',
             'product_ids.*' => 'required|exists:products,id',
             'quantities'    => 'required|array|min:1',
             'quantities.*'  => 'required|integer|min:1',
             'invoice_date'  => 'required|date',
             'paid_amount'   => 'required|numeric|min:0',
         ]);

         $oldItems = $invoice->items;
         if (!is_array($oldItems) || empty($oldItems)) {
             $oldItems = [
                 [
                     'product_id' => $invoice->product_id,
                     'quantity'   => $invoice->quantity,
                 ],
             ];
         }

         $groupedOldItems = collect($oldItems)->groupBy('product_id')->map(function ($rows) {
             return $rows->sum('quantity');
         });

         foreach ($groupedOldItems as $productId => $quantity) {
             $quantity = intval($quantity);
             $product = products::find($productId);
             if (! $product) {
                 continue;
             }
             $product->increment('stock', $quantity);
             if ($product->total_sold >= $quantity) {
                 $product->decrement('total_sold', $quantity);
             } else {
                 $product->update(['total_sold' => 0]);
             }
         }

         $items = [];
         $totalAmount = 0;
         $totalQuantity = 0;

         foreach ($validated['product_ids'] as $index => $productId) {
             $quantity = intval($validated['quantities'][$index] ?? 1);
             $product = products::findOrFail($productId);
             $lineTotal = $product->price * $quantity;

             $items[] = [
                 'product_id' => $product->id,
                 'name'       => $product->name,
                 'price'      => $product->price,
                 'quantity'   => $quantity,
                 'line_total' => $lineTotal,
             ];

             $totalAmount += $lineTotal;
             $totalQuantity += $quantity;
         }

         $firstProduct = products::findOrFail($validated['product_ids'][0]);

         $invoice->update([
             'customer'      => $validated['customer'],
             'product_id'    => $firstProduct->id,
             'quantity'      => $totalQuantity,
             'invoice_date'  => $validated['invoice_date'],
             'total_amount'  => $totalAmount,
             'product_price' => $firstProduct->price,
             'paid_amount'   => $validated['paid_amount'],
             'status'        => $validated['paid_amount'] >= $totalAmount ? 'paid' : 'unpaid',
             'items'         => $items,
         ]);

         foreach ($items as $item) {
             $product = products::find($item['product_id']);
             if ($product) {
                 $product->decrement('stock', $item['quantity']);
                 $product->increment('total_sold', $item['quantity']);
             }
         }

         $this->syncInvoiceInstallment($invoice, $items, $totalAmount, floatval($validated['paid_amount']));

         // notify invoice update
         try {
             AppNotification::create([
                 'title' => 'تم تحديث فاتورة',
                 'message' => 'تم تحديث الفاتورة #' . $invoice->invoice_number,
                 'is_active' => true,
                 'created_by' => $request->user()->id,
             ]);
         } catch (\Exception $e) {
         }

         return redirect()->route('invoices.index')->with('success', 'تم تحديث الفاتورة بنجاح.');
     }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(invoice $invoice)
    {
        if (request()->user()->cannot('delete-invoice', Invoice::class)) {
            return redirect()->route('invoices.index')->with('error', 'You do not have permission to delete this invoice.');
        }
        // create notification for deletion
        try {
            AppNotification::create([
                'title' => 'تم حذف فاتورة',
                'message' => 'تم حذف الفاتورة #' . $invoice->invoice_number,
                'is_active' => true,
                'created_by' => request()->user()->id,
            ]);
        } catch (\Exception $e) {
        }

        invoice::findOrFail($invoice->id)->delete();
        return redirect()->route('invoices.index');
    }

    public function refund(invoice $invoice)
    {
        if (request()->user()->cannot('refund-invoice', $invoice)) {
            return redirect()->route('invoices.index')->with('error', 'You do not have permission to refund this invoice.');
        }

        if ($invoice->status === 'refunded') {
            return redirect()->back()->with('error', 'This invoice has already been refunded.');
        }

        $product = products::findOrFail($invoice->product_id);
        $product->update([
            'stock' => $product->stock + $invoice->quantity,
            'total_sold' => max(0, $product->total_sold - $invoice->quantity),
        ]);

        $invoice->update([
            'status' => 'refunded',
        ]);

        try {
            AppNotification::create([
                'title' => 'تم استرجاع فاتورة',
                'message' => 'تم استرجاع الفاتورة #' . $invoice->invoice_number,
                'is_active' => true,
                'created_by' => request()->user()->id,
            ]);
        } catch (\Exception $e) {
        }

        return redirect()->route('invoices.index')->with('success', 'Invoice refunded successfully.');
    }

    public function print(invoice $invoice)
    {
        $invoice->load('product');
        return view('invoice.print', ['invoice' => $invoice]);
    }

    protected function syncInvoiceInstallment(invoice $invoice, array $items, float $totalAmount, float $paidAmount): void
    {
        $remaining = max(0, $totalAmount - $paidAmount);
        $status = $remaining <= 0 ? 'مكتمل' : 'غير مكتمل';

        $installmentData = [
            'invoice_id'        => $invoice->id,
            'customer'          => $invoice->customer,
            'product_id'        => $invoice->product_id,
            'product_name'      => collect($items)->pluck('name')->implode(', '),
            'product_price'     => $totalAmount,
            'quantity'          => $invoice->quantity,
            'payment_date'      => null,
            'next_payment_date' => $remaining > 0 ? Carbon::now()->addMonth()->format('Y-m-d') : null,
            'paid_amount'       => $paidAmount,
            'remaining'         => $remaining,
            'status'            => $status,
            'items'             => $items,
        ];

        $existingInstallment = Installments::where('invoice_id', $invoice->id)->first();

        if ($existingInstallment) {
            $existingInstallment->update($installmentData);
            // notify installment update
            try {
                AppNotification::create([
                    'title' => 'تم تحديث قسط',
                    'message' => 'تم تحديث القسط المرتبط بالفاتورة #' . $invoice->invoice_number,
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]);
            } catch (\Exception $e) {
            }

            return;
        }

        if ($remaining > 0) {
            $inst = Installments::create($installmentData);
            try {
                AppNotification::create([
                    'title' => 'تم إضافة دين',
                    'message' => 'تم إضافة دين للفاتورة #' . $invoice->invoice_number,
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]);
            } catch (\Exception $e) {
            }
        }
    }
}
