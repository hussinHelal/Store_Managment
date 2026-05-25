<?php

namespace App\Http\Controllers;

use App\Models\customers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = customers::orderBy('id', 'desc')->paginate(15);
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
         if ($request->user()->cannot('create-customers', customers::class)) {
             return redirect()->route('customers.index')->with('error','هذا المستخدم ليس لديه صلاحيه لهذا الامر');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $customers = customers::create($validated);
        return redirect()->route('customers.index');
    }

    /**
     * Display the specified resource.
     */
    // public function show(customers $customers)
    // {
    //     return view('customers.show', ['customers' => $customers]);
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(customers $customer)
    {
        return view('customers.edit', ['customers' => $customer]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, customers $customer)
    {
        if ($request->user()->cannot('update-customers', $customer)) {
            return redirect()->route('customers.index')->with('error','هذا المستخدم ليس لديه صلاحيه لهذا الامر');
        }
        Log::info('Customer updating', ['id' => $customer->id]);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
        ]);

        $customer->update($validated);
        Log::info('Customer updated', ['id' => $customer->id]);
        return redirect()->route('customers.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, customers $customer)
    {
        if ($request->user()->cannot('delete-customers', $customer)) {
            return redirect()->route('customers.index')->with('error','هذا المستخدم ليس لديه صلاحيه لهذا الامر');
        }
        Log::info('Customer deleting', ['id' => $customer->id]);
        $customer->delete();
        return redirect()->route('customers.index');
    }
}
