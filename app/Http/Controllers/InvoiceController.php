<?php

namespace App\Http\Controllers;

use App\Models\invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = invoice::all();
        return view('invoices.index', ['invoices' => $invoices]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('invoices.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'invoice_number' => 'required',
            'customer' => 'required',
            'description' => 'required',
            'invoice_date' => 'required',
            'total_amount' => 'required',
            'status' => 'required',
        ]);
        invoice::create($validate);
        return redirect()->route('invoices.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(invoice $invoice)
    {
        return view('invoices.show', ['invoice' => $invoice]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(invoice $invoice)
    {
        return view('invoices.edit', ['invoice' => $invoice]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, invoice $invoice)
    {
        $validate = $request->validate([
            'invoice_number' => 'required',
            'customer' => 'required',
            'description' => 'required',
            'invoice_date' => 'required',
            'total_amount' => 'required',
            'status' => 'required',
        ]);
        $invoice->update($validate);
        return redirect()->route('invoices.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index');
    }
}
