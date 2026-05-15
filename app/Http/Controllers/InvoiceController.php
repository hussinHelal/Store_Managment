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
        $invoices = Invoice::with('products')->get();
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
        $validate = $request->validate([
            'customer' => 'required | string',
            'product_id' => 'required | exists:products,id',
            'quantity' => 'required | numeric|min:1',
            'invoice_date' => 'required | date',
            'total_amount' => 'required | numeric|min:1',
            'paid_amount' => 'required | numeric|min:1', 
        ]);
        $product = products::findOrFail($validate['product_id']);
        // dd($product, $validate);
        $invoice = invoice::create([
            'invoice_number' => 'INV-' . time(),
            'customer' => $validate['customer'],
            'product_id' => $validate['product_id'],
            'quantity' => $validate['quantity'],
            'product_price'  => $product->price,
            'invoice_date' => $validate['invoice_date'],
            'total_amount' => $product->price * $validate['quantity'],
            'paid_amount' => $validate['paid_amount'],
            'status' => $validate['paid_amount'] >= $validate['total_amount'] ? 'paid' : 'unpaid',
        ]);
            
        $product->update([
        'stock'      => $product->stock - $validate['quantity'],
        'total_sold' => $product->total_sold + $validate['quantity'],
        ]); 
        // $product_id = products::findOrFail($validate['product_id']);
        // $product_id->update(['stock' => $product_id->stock - $validate['quantity']]);

        // $increase_product_total_sold = products::findOrFail($validate['product_id']);
        // $product_id->update(['total_sold' => $product_id->total_sold + $validate['quantity']]);
        
        return redirect()->route('invoices.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(invoice $invoice)
    {
        return view('invoice.show', ['invoice' => $invoice]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(invoice $invoice)
    {
        return view('invoice.edit', ['invoice' => $invoice]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, invoice $invoice)
    {
        $validate = $request->validate([
            'customer' => 'required | string',
            'product_id' => 'required | exists:products,id',
            'product_price' => 'required | numeric|min:1',
            'quantity' => 'required | numeric|min:1',
            'invoice_date' => 'required | date',
            'total_amount' => 'required | numeric|min:1',
            'paid_amount' => 'required | numeric|min:1', 
        ]);

        if(!isDirty($request->all(), $invoice->getAttributes())){
            return redirect()->back()->withErrors(['message' => 'No changes detected']);
        }

        invoice::findOrFail($invoice->id)->update([
            'invoice_number' => 'INV-' . time(),
            'customer' => $validate['customer'],
            'product_id' => $validate['product_id'],
            'product_price' => $validate['product_price'],
            'quantity' => $validate['quantity'],
            'invoice_date' => $validate['invoice_date'],
            'total_amount' => invoice::findOrFail($validate['product_id'])->calculate_total_amount(),
            'paid_amount' => $validate['paid_amount'],
            'status' => $validate['paid_amount'] >= $validate['total_amount'] ? 'paid' : 'unpaid',
        ]);

        $product_id = products::findOrFail($validate['product_id']);
        $product_id->update(['stock' => $product_id->stock - $validate['quantity']]);

        // $increase_product_total_sold = products::findOrFail($validate['product_id']);
        $product_id->update(['total_sold' => $product_id->total_sold + $validate['quantity']]);
        return redirect()->route('invoices.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(invoice $invoice)
    {
        invoice::findOrFail($invoice->id)->delete();
        return redirect()->route('invoices.index');
    }
}
