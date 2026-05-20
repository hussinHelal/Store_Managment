<?php

namespace App\Http\Controllers;

use App\Models\installments;
use App\Models\products;
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
        // $installments = installments::all();
        // $products = products::all();
        $installments = Installments::with('product')->get(); 
        $products = products::all();
        return view('installments.index', ['installments' => $installments, 'products' => $products]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(products $products)
    {
        $product = $products::all();
        return view('installments.create', ['product' => $product]);
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
            'product_name' => 'required|string',
            'product_price' => 'required|numeric',
            'payment_date' => 'nullable|date',
            'next_payment_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric',
            'quantity' => 'nullable|numeric',
        ]);
       
        installments::create([
            'customer' => $validate['customer'],
            'product_name' => $validate['product_name'],
            'product_price' => $validate['product_price'],
            'quantity' => $validate['quantity'],
            'payment_date' => $validate['payment_date'] ? Carbon::parse($validate['payment_date'])->format('Y-m-d') : null,
            'next_payment_date' => $validate['next_payment_date'] ? Carbon::parse($validate['next_payment_date'])->format('Y-m-d') : null,
            'paid_amount' => $validate['paid_amount'],
            'remaining' => $validate['product_price'] * ($validate['quantity'] ?? 1) - $validate['paid_amount'],
        ]);

        Log::info('Installment created');
        
        return redirect()->route('installments.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(installments $installments)
    {
        //return view('installments.show', ['installments' => $installments]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(installments $installment, products $products)
    {
        $product = $products::all();
        return view('installments.edit', ['installment' => $installment, 'product' => $product]);
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
            'customer' => 'required',
            'product_name' => 'required|string',
            'product_price' => 'required|numeric',
            'payment_date' => 'nullable|date',
            'next_payment_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric',
            'quantity' => 'nullable|numeric',
        ]);
    
        $remaining = $validate['product_price'] * ($validate['quantity'] ?? 1) - $request->input('paid_amount', 0);
    
        $installments->update(array_merge($validate, ['remaining' => $remaining]));

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

    public function pay(Request $request, installments $installments)
    {
        $validate = $request->validate([
            'paid_amount' => 'required|numeric',
        ]);

        $installments->update([
            'paid_amount' => $installments->paid_amount + $validate['paid_amount'],
            'remaining' => $installments->remaining - $validate['paid_amount'],
        ]);

        return redirect()->route('installments.index');
    }

}
