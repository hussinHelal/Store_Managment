<?php

namespace App\Http\Controllers;

use App\Models\installments;
use App\Models\products;
use App\Models\AppNotification;
use App\Models\invoice;
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
        $installments = Installments::with('product')->orderBy('id', 'desc')->paginate(15);
        $products = products::all();
        return view('installments.index', ['installments' => $installments, 'products' => $products]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = products::all();
        return view('installments.create', ['products' => $products]);
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
             'product_ids'       => 'required|array|min:1',
             'product_ids.*'     => 'required|exists:products,id',
             'quantities'        => 'required|array|min:1',
             'quantities.*'      => 'required|integer|min:1',
             'payment_date'      => 'nullable|date',
             'next_payment_date' => 'nullable|date',
             'paid_amount'       => 'nullable|numeric|min:0',
         ]);

         $items = [];
         $totalQuantity = 0;
         $totalAmount = 0;

         foreach ($validate['product_ids'] as $index => $productId) {
             $quantity = intval($validate['quantities'][$index] ?? 1);
             $product = products::findOrFail($productId);
             $lineTotal = $product->price * $quantity;

             $items[] = [
                 'product_id' => $product->id,
                 'name'       => $product->name,
                 'price'      => $product->price,
                 'quantity'   => $quantity,
                 'line_total' => $lineTotal,
             ];

             $totalQuantity += $quantity;
             $totalAmount += $lineTotal;
         }

         $firstProduct = products::findOrFail($validate['product_ids'][0]);
         $paidAmount = $validate['paid_amount'] ?? 0;
         $status = ($paidAmount >= $totalAmount) ? 'مكتمل' : 'غير مكتمل';

         Installments::create([
             'customer'          => $validate['customer'],
             'product_id'        => $firstProduct->id,
             'product_name'      => collect($items)->pluck('name')->implode(', '),
             'product_price'     => $totalAmount,
             'quantity'          => $totalQuantity,
             'payment_date'      => $validate['payment_date'] ? Carbon::parse($validate['payment_date'])->format('Y-m-d') : null,
             'next_payment_date' => $validate['next_payment_date'] ? Carbon::parse($validate['next_payment_date'])->format('Y-m-d') : null,
             'paid_amount'       => $paidAmount,
             'remaining'         => $totalAmount - $paidAmount,
             'status'            => $status,
             'items'             => $items,
         ]);

        try {
            AppNotification::create([
                'title' => 'تم إضافة دين',
                'message' => 'تم إضافة دين للعميل ' . $validate['customer'],
                'is_active' => true,
                'created_by' => $request->user()->id,
            ]);
        } catch (\Exception $e) {
        }

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
    public function edit(installments $installment)
    {
        $products = products::all();
        return view('installments.edit', ['installment' => $installment, 'products' => $products]);
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
             'product_ids'       => 'required|array|min:1',
             'product_ids.*'     => 'required|exists:products,id',
             'quantities'        => 'required|array|min:1',
             'quantities.*'      => 'required|integer|min:1',
             'payment_date'      => 'nullable|date',
             'next_payment_date' => 'nullable|date',
             'paid_amount'       => 'nullable|numeric|min:0',
         ]);

         $items = [];
         $totalQuantity = 0;
         $totalAmount = 0;

         foreach ($validate['product_ids'] as $index => $productId) {
             $quantity = intval($validate['quantities'][$index] ?? 1);
             $product = products::findOrFail($productId);
             $lineTotal = $product->price * $quantity;

             $items[] = [
                 'product_id' => $product->id,
                 'name'       => $product->name,
                 'price'      => $product->price,
                 'quantity'   => $quantity,
                 'line_total' => $lineTotal,
             ];

             $totalQuantity += $quantity;
             $totalAmount += $lineTotal;
         }

         $firstProduct = products::findOrFail($validate['product_ids'][0]);
         $paidAmount = $validate['paid_amount'] ?? 0;
         $status = ($paidAmount >= $totalAmount) ? 'مكتمل' : 'غير مكتمل';

         $installment->update([
             'customer'          => $validate['customer'],
             'product_id'        => $firstProduct->id,
             'product_name'      => collect($items)->pluck('name')->implode(', '),
             'product_price'     => $totalAmount,
             'quantity'          => $totalQuantity,
             'payment_date'      => $validate['payment_date'] ? Carbon::parse($validate['payment_date'])->format('Y-m-d') : null,
             'next_payment_date' => $validate['next_payment_date'] ? Carbon::parse($validate['next_payment_date'])->format('Y-m-d') : null,
             'paid_amount'       => $paidAmount,
             'remaining'         => $totalAmount - $paidAmount,
             'status'            => $status,
             'items'             => $items,
         ]);

        try {
            AppNotification::create([
                'title' => 'تم تحديث دين',
                'message' => 'تم تحديث الدين #' . $installment->id,
                'is_active' => true,
                'created_by' => $request->user()->id,
            ]);
        } catch (\Exception $e) {
        }

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
        try {
            AppNotification::create([
                'title' => 'تم حذف دين',
                'message' => 'تم حذف الدين #' . $installment->id,
                'is_active' => true,
                'created_by' => request()->user()->id,
            ]);
        } catch (\Exception $e) {
        }

        $installment->delete();

        return redirect()->route('installments.index');
    }

    public function showPay($id)
    {
        $installment = installments::findOrFail($id);
        return view('installments.pay', compact('installment'));
    }

    public function pay(Request $request, installments $installment)
    {
        $validated = $request->validate([
            'paid_amount' => 'required|numeric|min:1',
        ]);

        $newPaidAmount  = $installment->paid_amount + $validated['paid_amount'];
        $newRemaining   = $installment->remaining   - $validated['paid_amount'];
        $newStatus      = $newRemaining <= 0 ? 'مكتمل' : 'غير مكتمل';
        $newNextPayment = $newStatus === 'مكتمل' ? null : Carbon::now()->addMonth();

        $installment->update([
            'paid_amount'      => $newPaidAmount,
            'remaining'        => $newRemaining,
            'status'           => $newStatus,
            'paid_at'          => Carbon::now(),
            'next_payment_date'=> $newNextPayment,
        ]);

        try {
            AppNotification::create([
                'title' => 'تم استلام دفعة',
                'message' => 'تم استلام دفعة بمبلغ ' . $validated['paid_amount'] . ' على الدين #' . $installment->id,
                'is_active' => true,
                'created_by' => $request->user()->id,
            ]);
        } catch (\Exception $e) {
        }

        // Sync invoice paid status and amount
        try {
            $inv = $installment->invoice;
            if ($inv) {
                $inv->paid_amount = ($inv->paid_amount ?? 0) + $validated['paid_amount'];
                if ($inv->paid_amount >= $inv->total_amount) {
                    $inv->paid_amount = $inv->total_amount;
                    $inv->status = 'paid';
                    try {
                        AppNotification::create([
                            'title' => 'تم دفع الفاتورة',
                            'message' => 'تم دفع الفاتورة #' . $inv->invoice_number,
                            'is_active' => true,
                            'created_by' => $request->user()->id,
                        ]);
                    } catch (\Exception $e) {
                    }
                }
                $inv->save();
            }
        } catch (\Exception $e) {
        }

        return redirect()->route('installments.index');
    }

}
