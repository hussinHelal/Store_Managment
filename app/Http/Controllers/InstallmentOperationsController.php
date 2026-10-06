<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\installments as Installment;
use App\Models\invoice as Invoice;
use App\Models\products as Product;
use App\Services\CustomerLedgerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstallmentOperationsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['search' => ['nullable', 'string', 'max:80']]);
        $installments = Installment::with('product')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->toString().'%';
                $query->where(fn ($query) => $query
                    ->where('customer', 'like', $search)
                    ->orWhere('product_name', 'like', $search));
            })
            ->orderByDesc('id')->paginate(20)->withQueryString();
        return view('installments.index', compact('installments'));
    }

    public function create()
    {
        $oldProductIds = old('product_ids', []);
        $products = Product::query()->whereIn('id', is_array($oldProductIds) ? $oldProductIds : [])
            ->select(['id', 'name', 'price'])->get();
        $installmentProducts = $products->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
        ])->all();

        return view('installments.create', compact('products', 'installmentProducts'));
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $validated = $request->validate(['q' => ['required', 'string', 'max:100']]);
        $term = trim($validated['q']);

        if (mb_strlen($term) < 2) {
            return response()->json(['data' => []]);
        }

        $like = '%'.addcslashes($term, '%_\\').'%';
        $products = Product::query()
            ->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('barcode', 'like', $like))
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'price']);

        return response()->json(['data' => $products]);
    }

    public function show(Installment $installment)
    {
        $installment->load('product');

        return view('installments.show', compact('installment'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedInput($request);
        $userId = $request->user()->id;

        DB::transaction(function () use ($validated, $userId): void {
            [$items, $totalQuantity, $totalCents, $products] = $this->buildItems($validated);
            $paidCents = (int) round((float) $validated['paid_amount'] * 100);

            if ($paidCents > $totalCents) {
                throw ValidationException::withMessages(['paid_amount' => 'لا يمكن أن يتجاوز المبلغ المدفوع إجمالي القسط.']);
            }

            $firstProduct = $products->get($validated['product_ids'][0]);
            $complete = $paidCents === $totalCents;

            $installment = Installment::create([
                'customer' => $validated['customer'],
                'product_id' => $firstProduct->id,
                'product_name' => collect($items)->pluck('name')->implode(', '),
                'product_price' => number_format($totalCents / 100, 2, '.', ''),
                'quantity' => $totalQuantity,
                'payment_date' => $validated['payment_date'] ?? null,
                'next_payment_date' => $complete ? null : ($validated['next_payment_date'] ?? null),
                'paid_amount' => number_format($paidCents / 100, 2, '.', ''),
                'remaining' => number_format(($totalCents - $paidCents) / 100, 2, '.', ''),
                'status' => $complete ? 'مكتمل' : 'غير مكتمل',
                'items' => $items,
                'notes' => $validated['notes'] ?? null,
            ]);

            if (! $complete) {
                app(CustomerLedgerService::class)->syncNamedCustomer($validated['customer'], $installment);
            }

            AppNotification::create([
                'title' => 'تم إضافة دين',
                'message' => 'تم إضافة دين للعميل '.$validated['customer'],
                'is_active' => true,
                'created_by' => $userId,
            ]);
        }, 3);

        return redirect()->route('installments.index')->with('success', 'تم إضافة الدين بنجاح');
    }

    public function edit(Installment $installment)
    {
        abort_if($installment->invoice_id, 403, 'يجب تعديل الأقساط المرتبطة بفاتورة من صفحة الفاتورة.');
        $productIds = old('product_ids');
        if (! is_array($productIds)) {
            $productIds = is_array($installment->items) && $installment->items !== []
                ? collect($installment->items)->pluck('product_id')->all()
                : [$installment->product_id];
        }
        $products = Product::query()->whereIn('id', array_filter($productIds))
            ->select(['id', 'name', 'price'])->get();
        $installmentProducts = $products->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
        ])->all();

        return view('installments.edit', compact('installment', 'products', 'installmentProducts'));
    }

    public function update(Request $request, Installment $installment)
    {
        abort_if($installment->invoice_id, 403, 'يجب تعديل الأقساط المرتبطة بفاتورة من صفحة الفاتورة.');
        $validated = $this->validatedInput($request);
        $userId = $request->user()->id;

        DB::transaction(function () use ($validated, $installment, $userId): void {
            $lockedInstallment = Installment::query()->lockForUpdate()->findOrFail($installment->id);
            abort_if($lockedInstallment->invoice_id, 403);
            [$items, $totalQuantity, $totalCents, $products] = $this->buildItems($validated);
            $paidCents = (int) round((float) $validated['paid_amount'] * 100);

            if ($paidCents > $totalCents) {
                throw ValidationException::withMessages(['paid_amount' => 'لا يمكن أن يتجاوز المبلغ المدفوع إجمالي القسط.']);
            }

            $firstProduct = $products->get($validated['product_ids'][0]);
            $complete = $paidCents === $totalCents;
            $lockedInstallment->update([
                'customer' => $validated['customer'],
                'product_id' => $firstProduct->id,
                'product_name' => collect($items)->pluck('name')->implode(', '),
                'product_price' => number_format($totalCents / 100, 2, '.', ''),
                'quantity' => $totalQuantity,
                'payment_date' => $validated['payment_date'] ?? null,
                'next_payment_date' => $complete ? null : ($validated['next_payment_date'] ?? null),
                'paid_amount' => number_format($paidCents / 100, 2, '.', ''),
                'remaining' => number_format(($totalCents - $paidCents) / 100, 2, '.', ''),
                'status' => $complete ? 'مكتمل' : 'غير مكتمل',
                'items' => $items,
                'notes' => $validated['notes'] ?? $lockedInstallment->notes,
            ]);

            if (! $complete) {
                app(CustomerLedgerService::class)->syncNamedCustomer($validated['customer'], $lockedInstallment);
            }

            AppNotification::create([
                'title' => 'تم تحديث دين',
                'message' => 'تم تحديث الدين #'.$lockedInstallment->id,
                'is_active' => true,
                'created_by' => $userId,
            ]);
        }, 3);

        return redirect()->route('installments.index')->with('success', 'تم تحديث الدين بنجاح');
    }

    public function destroy(Installment $installment, Request $request)
    {
        if ($installment->invoice_id) {
            return redirect()->route('installments.index')->with('error', 'لا يمكن حذف سجل أقساط مرتبط بفاتورة.');
        }

        DB::transaction(function () use ($installment, $request): void {
            $lockedInstallment = Installment::query()->lockForUpdate()->findOrFail($installment->id);
            if ($lockedInstallment->invoice_id) {
                throw ValidationException::withMessages(['installment' => 'لا يمكن حذف سجل أقساط مرتبط بفاتورة.']);
            }

            AppNotification::create([
                'title' => 'تم حذف دين',
                'message' => 'تم حذف الدين #'.$lockedInstallment->id,
                'is_active' => true,
                'created_by' => $request->user()->id,
            ]);
            $lockedInstallment->delete();
        }, 3);

        return redirect()->route('installments.index')->with('success', 'تم حذف الدين بنجاح');
    }

    public function showPay(int $id)
    {
        $installment = Installment::findOrFail($id);
        if ($installment->status === 'مكتمل' || (float) $installment->remaining <= 0) {
            return redirect()->route('installments.index')->with('error', 'هذا القسط مكتمل ولا يحتاج إلى دفعة أخرى.');
        }

        return view('installments.pay', compact('installment'));
    }

    public function pay(Request $request, Installment $installment)
    {
        $validated = $request->validate([
            'paid_amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01'],
        ]);
        $paymentCents = (int) round((float) $validated['paid_amount'] * 100);
        $userId = $request->user()->id;

        DB::transaction(function () use ($installment, $paymentCents, $userId): void {
            $lockedInstallment = Installment::query()->lockForUpdate()->findOrFail($installment->id);
            $remainingCents = (int) round((float) $lockedInstallment->remaining * 100);

            if ($lockedInstallment->status === 'مكتمل' || $remainingCents <= 0) {
                throw ValidationException::withMessages(['paid_amount' => 'هذا القسط مكتمل ولا يحتاج إلى دفعة أخرى.']);
            }

            if ($paymentCents > $remainingCents) {
                throw ValidationException::withMessages(['paid_amount' => 'لا يمكن أن تتجاوز الدفعة الرصيد المتبقي.']);
            }

            $paidCents = (int) round((float) $lockedInstallment->paid_amount * 100) + $paymentCents;
            $remainingCents -= $paymentCents;
            $complete = $remainingCents === 0;
            $lockedInstallment->update([
                'paid_amount' => number_format($paidCents / 100, 2, '.', ''),
                'remaining' => number_format($remainingCents / 100, 2, '.', ''),
                'status' => $complete ? 'مكتمل' : 'غير مكتمل',
                'payment_date' => now()->toDateString(),
                'next_payment_date' => $complete ? null : now()->addMonth()->toDateString(),
            ]);

            if ($lockedInstallment->invoice_id) {
                $invoice = Invoice::query()->lockForUpdate()->findOrFail($lockedInstallment->invoice_id);
                $invoicePaidCents = (int) round((float) $invoice->paid_amount * 100);
                $invoiceTotalCents = (int) round((float) $invoice->total_amount * 100);

                if ($invoice->status === 'refunded' || $paymentCents > max(0, $invoiceTotalCents - $invoicePaidCents)) {
                    throw ValidationException::withMessages(['paid_amount' => 'لا يمكن تسجيل هذه الدفعة على الفاتورة.']);
                }

                $invoice->paid_amount = number_format(($invoicePaidCents + $paymentCents) / 100, 2, '.', '');
                $invoice->status = $invoicePaidCents + $paymentCents >= $invoiceTotalCents ? 'paid' : 'unpaid';
                $invoice->save();
            }

            AppNotification::create([
                'title' => 'تم استلام دفعة',
                'message' => 'تم استلام دفعة بمبلغ '.number_format($paymentCents / 100, 2, '.', '').' على الدين #'.$lockedInstallment->id,
                'is_active' => true,
                'created_by' => $userId,
            ]);
        }, 3);

        return redirect()->route('installments.index')->with('success', 'تم تسجيل الدفعة بنجاح.');
    }

    private function validatedInput(Request $request): array
    {
        $productIds = $request->input('product_ids', []);
        $count = is_array($productIds) ? count($productIds) : 0;

        return $request->validate([
            'customer' => ['required', 'string', 'max:255'],
            'product_ids' => ['required', 'array', 'min:1', 'max:100'],
            'product_ids.*' => ['required', 'integer'],
            'quantities' => ['required', 'array', 'size:'.$count, 'max:100'],
            'quantities.*' => ['required', 'integer', 'min:1', 'max:100000'],
            'payment_date' => ['nullable', 'date'],
            'next_payment_date' => ['nullable', 'date'],
            'paid_amount' => ['required', 'numeric', 'decimal:0,2', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], ['notes.max' => 'يجب ألا تتجاوز الملاحظات 2000 حرف.']);
    }

    private function buildItems(array $validated): array
    {
        $productIds = array_values(array_unique($validated['product_ids']));
        $products = Product::query()->whereIn('id', $productIds)->orderBy('id')
            ->lockForUpdate()->get()->keyBy('id');

        if ($products->count() !== count($productIds)) {
            throw ValidationException::withMessages(['product_ids' => 'أحد المنتجات المحددة لم يعد متاحاً.']);
        }

        $items = [];
        $totalQuantity = 0;
        $totalCents = 0;

        foreach ($validated['product_ids'] as $index => $productId) {
            $product = $products->get($productId);
            $quantity = (int) $validated['quantities'][$index];
            $priceCents = (int) round((float) $product->price * 100);
            $lineTotalCents = $priceCents * $quantity;
            $items[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => number_format($priceCents / 100, 2, '.', ''),
                'quantity' => $quantity,
                'line_total' => number_format($lineTotalCents / 100, 2, '.', ''),
            ];
            $totalQuantity += $quantity;
            $totalCents += $lineTotalCents;
        }

        return [$items, $totalQuantity, $totalCents, $products];
    }
}
