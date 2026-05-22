<?php

namespace App\Http\Controllers;

use App\Models\invoice;
use App\Models\products;
use App\Models\sales;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $soldProducts = products::select('products.id', 'products.name')
            ->join('invoices', 'products.id', '=', 'invoices.product_id')
            ->where('invoices.status', '!=', 'refunded')
            ->selectRaw('SUM(invoices.quantity) as sold_quantity')
            ->groupBy('products.id', 'products.name')
            ->get();

        return view('sales.index', ['soldProducts' => $soldProducts]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sales.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validate = $request->validate([
            'name' => 'required',
            'quantity' => 'required',
            'description' => 'required',
        ]);
        sales::create($validate);
        return redirect()->route('sales.index');
    }

    /**
     * Display the specified resource.
     */
    // public function show(sales $sales)
    // {
    //     $products = products::all();
    //     return view('sales.show', ['sales' => $sales, 'products' => $products]); 
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(sales $sales)
    {
        return view('sales.edit', ['sales' => $sales]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, sales $sales)
    {
        $validate = $request->validate([
            'name' => 'required',
            'quantity' => 'required',
            'description' => 'required',
        ]);
        $sales->update($validate);
        return redirect()->route('sales.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(sales $sales)
    {
        $sales->delete();
        return redirect()->route('sales.index');
    }
}
