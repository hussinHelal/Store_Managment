<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\products;
use App\Models\category;
use Illuminate\Support\Facades\Log;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $products = products::all();
        $products = products::with('category')->get();
        $categories = category::all();
        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = category::all();
        return view('products.create', ['categories' => $categories]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required | string',
            'price' => 'required | numeric',
            'description' => 'required | string',
            'stock' => 'required | numeric',
            'category_id' => 'required|exists:categories,id',
        ]);
        
        products::create($validate);
        

        return redirect()->route('products.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(products $products)
    {
        //return view('products.show', ['products' => $products]);
        return redirect()->route('products.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(products $product)
    {
        $categories = category::all();
        return view('products.edit', ['product' => $product, 'categories' => $categories,'id' => $product->id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, products $products,int $id)
    {
         if ($request->user()->cannot('update-products', $products)) {
            abort(403);
        }
        $validate = $request->validate([
            'name' => 'required | string ',
            'price' => 'required | numeric',
            'description' => 'required | string',
            'stock' => 'required | numeric',
            'category_id' => 'required|exists:categories,id',
        ]);
        products::where('id', $id)->update($validate);
        return redirect()->route('products.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->cannot('delete-products', Products::class)) {
            abort(403);
        }
         $products = products::findOrFail($id);
        $products = products::findOrFail($id);
        $products->delete();
        return redirect()->route('products.index');
    }
}
