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
             return redirect()->route('installments.index')
                 ->with('error', 'You do not have permission to create installments.');
         }
     
         $validate = $request->validate([
             'customer'          => 'required|string|max:255',
             'product_id'        => 'required|exists:products,id',
             'payment_date'      => 'nullable|date',
             'next_payment_date' => 'nullable|date',
             'paid_amount'       => 'nullable|numeric|min:0',
             'quantity'          => 'nullable|integer|min:1',
         ]);
     
         $product = Products::findOrFail($validate['product_id']);
     
         $quantity     = $validate['quantity'] ?? 1;
         $totalAmount  = $product->price * $quantity;
         $paidAmount   = $validate['paid_amount'] ?? 0;
     
         $status = ($paidAmount >= $totalAmount) ? 'مكتمل' : 'غير مكتمل';
     
         Installments::create([
             'customer'          => $validate['customer'],
             'product_id'        => $product->id,
             'product_name'      => $product->name,
             'product_price'     => $product->price,
             'quantity'          => $quantity,
             'payment_date'      => $validate['payment_date'] ? Carbon::parse($validate['payment_date'])->format('Y-m-d') : null,
             'next_payment_date' => $validate['next_payment_date'] ? Carbon::parse($validate['next_payment_date'])->format('Y-m-d') : null,
             'paid_amount'       => $paidAmount,
             'remaining'         => $totalAmount - $paidAmount,
             'status'            => $status,          
         ]);
     
         return redirect()->route('installments.index')
             ->with('success', 'تم إضافة الدين بنجاح');
     }
    /**
     * Display the specified resource.
     */
    // public function show(installments $installments)
    // {
    //     //return view('installments.show', ['installments' => $installments]);
    // }

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
     public function update(Request $request, Installments $installment)
     {
         if ($request->user()->cannot('update-installments', $installment)) {
             return redirect()->route('installments.index')
                 ->with('error', 'You do not have permission to update installments.');
         }
     
         $validate = $request->validate([
             'customer'          => 'required|string|max:255',
             'product_id'        => 'required|exists:products,id',
             'payment_date'      => 'nullable|date',
             'next_payment_date' => 'nullable|date',
             'paid_amount'       => 'nullable|numeric|min:0',
             'quantity'          => 'nullable|integer|min:1',
         ]);
     
         $product = Products::findOrFail($validate['product_id']);
     
         $quantity     = $validate['quantity'] ?? 1;
         $totalAmount  = $product->price * $quantity;
         $paidAmount   = $validate['paid_amount'] ?? 0;
     
         $status = ($paidAmount >= $totalAmount) ? 'مكتمل' : 'غير مكتمل';
     
         $installment->update([
             'customer'          => $validate['customer'],
             'product_id'        => $product->id,
             'product_name'      => $product->name,
             'product_price'     => $product->price,
             'quantity'          => $quantity,
             'payment_date'      => $validate['payment_date'] ? Carbon::parse($validate['payment_date'])->format('Y-m-d') : null,
             'next_payment_date' => $validate['next_payment_date'] ? Carbon::parse($validate['next_payment_date'])->format('Y-m-d') : null,
             'paid_amount'       => $paidAmount,
             'remaining'         => $totalAmount - $paidAmount,
             'status'            => $status,               
         ]);
     
         return redirect()->route('installments.index')
             ->with('success', 'تم تحديث الدين بنجاح');
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
