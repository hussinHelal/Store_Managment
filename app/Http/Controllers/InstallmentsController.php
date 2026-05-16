<?php

namespace App\Http\Controllers;

use App\Models\installments;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
         if ($request->user()->cannot('create-installments', Installments::class)) {
            abort(403);
        }
        $validate = $request->validate([
            'customer' => 'required|string',
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
            'payment_date' => 'nullable|date',
            'next_payment_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric',
        ]);

        installments::create([
            'customer' => $validate['customer'],
            'amount' => $validate['amount'],
            'due_date' => Carbon::parse($validate['due_date'])->format('Y-m-d'),
            'payment_date' => $validate['payment_date'] ? Carbon::parse($validate['payment_date'])->format('Y-m-d') : null,
            'next_payment_date' => $validate['next_payment_date'] ? Carbon::parse($validate['next_payment_date'])->format('Y-m-d') : null,
            'paid_amount' => $validate['paid_amount'],
        ]);

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
        if ($request->user()->cannot('update-installments', $installments)) {
            abort(403);
        }
         
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
    public function destroy($id)
    {
        if (request()->user()->cannot('delete-installments', Installments::class)) {
            abort(403);
        }
        Log::info('Deleting installment: ' . $id);
        $installment = installments::findOrFail($id);
        $installment->delete();
        
        return redirect()->route('installments.index');
    }
}
