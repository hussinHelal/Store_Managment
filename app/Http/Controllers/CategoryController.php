<?php

namespace App\Http\Controllers;

use App\Models\category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = category::orderBy('name')->paginate(15);
        return view('category.index', ['categories' => $categories]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         if ($request->user()->cannot('create-category', Category::class)) {
            abort(403);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = category::create($validated);
        return redirect()->route('categories.index');
    }

    /**
     * Display the specified resource.
     */
    // public function show(category $category)
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(category $category)
    {
        return view('category.edit', ['category' => $category]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, category $category)
    {
        if ($request->user()->cannot('update-category', $category)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category->update($validated);
        return redirect()->route('categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, category $category)
    {
        if ($request->user()->cannot('delete-category', $category)) {
            abort(403);
        }

        $category->products()->update(['category_id' => null]);
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
