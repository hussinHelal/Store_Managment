<?php

namespace App\Http\Controllers;

use App\Models\installments;
use Illuminate\Http\Request;

class InstallmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $installments = installments::all();
        return view('installments.index', ['installments' => $installments]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('installments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'customer_id' => 'required',
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
            'payment_data' => 'nullable|string',
            'next_payment_date' => 'nullable|date',
            'payment_date' => 'nullable|date',
        ]);

        installments::create($validate);

        return redirect()->route('installments.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(installments $installments)
    {
        return view('installments.show', ['installments' => $installments]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(installments $installments)
    {
        return view('installments.edit', ['installments' => $installments]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, installments $installments)
    {
        $validate = $request->validate([
            'customer_id' => 'required',
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
            'payment_data' => 'nullable|string',
            'next_payment_date' => 'nullable|date',
            'payment_date' => 'nullable|date',
        ]);

        $installments->update($validate);

        return redirect()->route('installments.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(installments $installments)
    {
        $installments->delete();

        return redirect()->route('installments.index');
    }
}
