<?php

namespace App\Http\Controllers;

use App\Models\sales;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = sales::all();
        return view('sales.index', ['sales' => $sales]);
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
    public function show(sales $sales)
    {
        return view('sales.show', ['sales' => $sales]); 
    }

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
