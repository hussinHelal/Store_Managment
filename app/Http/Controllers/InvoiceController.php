<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\invoice;
use App\Models\products;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::with('product')->get();
        // $products = Invoice::with('products')->get()->pluck('products', 'id');
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
     
         $validated = $request->validate([
             'customer'     => 'required|string',
             'product_id'   => 'required|exists:products,id',
             'quantity'     => 'required|numeric|min:1',
             'invoice_date' => 'required|date',
             'paid_amount'  => 'required|numeric|min:0',
         ]);
     
         $product      = products::findOrFail($validated['product_id']);
         $total_amount = $product->price * $validated['quantity'];
     
         invoice::create([
             'invoice_number' => 'INV-' . time(),
             'customer'       => $validated['customer'],
             'product_id'     => $validated['product_id'],
             'quantity'       => $validated['quantity'],
             'invoice_date'   => $validated['invoice_date'],
             'total_amount'   => $total_amount,
             'paid_amount'    => $validated['paid_amount'],
             'status'         => $validated['paid_amount'] >= $total_amount ? 'paid' : 'unpaid',
         ]);
     
         $product->decrement('stock', $validated['quantity']);
         $product->increment('total_sold', $validated['quantity']);
     
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
     
         $validated = $request->validate([
             'customer'     => 'required|string',
             'product_id'   => 'required|exists:products,id',
             'quantity'     => 'required|numeric|min:1',
             'invoice_date' => 'required|date',
             'paid_amount'  => 'required|numeric|min:0',
         ]);
     
         $product = products::findOrFail($validated['product_id']);
         $total_amount = $product->price * $validated['quantity'];
     
         // Restore old stock before applying new quantity
         // $oldProduct = products::findOrFail($invoice->product_id);
         // $oldProduct->increment('stock', $invoice->quantity);
         // $oldProduct->decrement('total_sold', $invoice->quantity);

         $oldProduct = products::findOrFail($invoice->product_id);
         $oldProduct->increment('stock', $invoice->quantity);
         
         // Only decrement total_sold if it won't go below 0
         if ($oldProduct->total_sold >= $invoice->quantity) {
             $oldProduct->decrement('total_sold', $invoice->quantity);
         } else {
             $oldProduct->update(['total_sold' => 0]);
         }
         
         // Now decrement with new quantity
         $product->decrement('stock', $validated['quantity']);
         $product->increment('total_sold', $validated['quantity']);
     
         $invoice->update([
             'customer'     => $validated['customer'],
             'product_id'   => $validated['product_id'],
             'quantity'     => $validated['quantity'],
             'invoice_date' => $validated['invoice_date'],
             'total_amount' => $total_amount,
             'paid_amount'  => $validated['paid_amount'],
             'status'       => $validated['paid_amount'] >= $total_amount ? 'paid' : 'unpaid',
         ]);
     
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

        return redirect()->route('invoices.index')->with('success', 'Invoice refunded successfully.');
    }
}
