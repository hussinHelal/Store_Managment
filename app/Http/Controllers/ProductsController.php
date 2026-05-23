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
            'name' => 'required|string',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'stock' => 'required|numeric',
            'category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $folder = public_path('uploads/products');
            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);
            }
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\.\-]/', '_', $image->getClientOriginalName());
            $image->move($folder, $filename);
            $validate['image'] = $filename;
        }

        products::create($validate);

        return redirect()->route('products.index');
    }

    /**
     * Display the specified resource.
     */
    // public function show(products $products)
    // {
    //     //return view('products.show', ['products' => $products]);
    //     return redirect()->route('products.index');
    // }

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
    public function update(Request $request, products $product)
    {
         if ($request->user()->cannot('update-products', $product)) {
             return redirect()->route('products.index')->with('error','هذا المستخدم ليس لديه صلاحيه لهذا الامر');
        }
        $validate = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'stock' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $folder = public_path('uploads/products');
            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);
            }
            if ($product->image && file_exists($folder . DIRECTORY_SEPARATOR . $product->image)) {
                @unlink($folder . DIRECTORY_SEPARATOR . $product->image);
            }
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\.\-]/', '_', $image->getClientOriginalName());
            $image->move($folder, $filename);
            $validate['image'] = $filename;
        }

        $product->update($validate);
        return redirect()->route('products.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, products $product)
    {
        if ($request->user()->cannot('delete-products', $product)) {
            return redirect()->route('products.index')->with('error','هذا المستخدم ليس لديه صلاحيه لهذا الامر');
        }

        $folder = public_path('uploads/products');
        if ($product->image && file_exists($folder . DIRECTORY_SEPARATOR . $product->image)) {
            @unlink($folder . DIRECTORY_SEPARATOR . $product->image);
        }

        $product->invoices()->delete();
        $product->delete();
        return redirect()->route('products.index');
    }
}
