<?php

namespace App\Http\Controllers;

use App\Models\products;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = products::all();
        return view('products.index', ['products' => $products]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required',
            'price' => 'required',
            'quantity' => 'required',
            'description' => 'required',
            'stock' => 'required',
        ]);
        products::create($validate);
        return redirect()->route('products.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(products $products)
    {
        return view('products.show', ['products' => $products]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(products $products)
    {
        return view('products.edit', ['products' => $products]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, products $products)
    {
        $validate = $request->validate([
            'name' => 'required',
            'price' => 'required',
            'quantity' => 'required',
            'description' => 'required',
            'stock' => 'required',
        ]);
        $products->update($validate);
        return redirect()->route('products.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(products $products)
    {
        $products->delete();
        return redirect()->route('products.index');
    }
}
