<?php

namespace App\Http\Controllers;

use App\Models\products;
use App\Models\sales;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    /**
     * Sold-products report.
     *
     * Reads products.total_sold, which InvoiceCreationService increments for EVERY
     * line of an invoice and the update/delete/refund actions keep correct. The old
     * query joined invoices.product_id, which only holds the FIRST product of each
     * invoice, so multi-item invoices were counted against the wrong product.
     */
    public function index(Request $request)
    {
        $request->validate(['search' => ['nullable', 'string', 'max:80']]);
        $term = trim((string) $request->input('search', ''));

        $soldProducts = products::query()
            ->select('id', 'name')
            ->selectRaw('total_sold as sold_quantity')
            ->where('total_sold', '>', 0)
            ->when($term !== '', function ($query) use ($term) {
                $query->where('name', 'like', '%'.addcslashes($term, '%_\\').'%');
            })
            ->orderByDesc('total_sold')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('sales.index', ['soldProducts' => $soldProducts]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('invoices.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return redirect()->route('invoices.create')
            ->with('error', 'Sales are recorded through the invoice workflow.');
    }

    /**
     * Display the specified resource.
     */
    public function show(sales $sale)
    {
        $sale->load(['products', 'customer']);

        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(sales $sales)
    {
        return redirect()->route('sales.index')->with('error', 'Completed sales cannot be edited here.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, sales $sales)
    {
        return redirect()->route('sales.index')->with('error', 'Completed sales cannot be edited here.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(sales $sales)
    {
        return redirect()->route('sales.index')->with('error', 'Completed sales records cannot be deleted.');
    }
}
