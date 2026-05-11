<?php

namespace App\Http\Controllers;

use App\Models\customers;
use Illuminate\Http\Request;
use function Clue\StreamFilter\register;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = customers::all();
        return view('customers.index', ['customers' => $customers]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $customers = customers::create($validated);
        return redirect()->route('customers.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(customers $customers)
    {
        return view('customers.show', ['customers' => $customers]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(customers $customers)
    {
        return view('customers.edit', ['customers' => $customers]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, customers $customers)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $customers->update($validated);
        return redirect()->route('customers.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(customers $customers)
    {
        $customers->delete();
        return redirect()->route('customers.index');
    }
}
