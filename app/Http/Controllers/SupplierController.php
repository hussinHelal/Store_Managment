<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePurchaseRequest;
use App\Http\Requests\SaveSupplierRequest;
use App\Http\Requests\StoreSupplierPaymentRequest;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $suppliers = Supplier::query()
            ->withCount('purchases')
            ->when($search !== '', function ($query) use ($search): void {
                $like = '%'.$search.'%';
                $query->where(function ($query) use ($like): void {
                    $query->where('name', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->orderByDesc('balance')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $totalDue = (float) Supplier::query()->sum('balance');

        return view('suppliers.index', compact('suppliers', 'search', 'totalDue'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(SaveSupplierRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $opening = (float) ($data['opening_balance'] ?? 0);

        Supplier::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'opening_balance' => number_format($opening, 2, '.', ''),
            'balance' => number_format($opening, 2, '.', ''),
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'تم إضافة المورد بنجاح.');
    }

    public function show(Supplier $supplier): View
    {
        $supplier->loadCount('purchases');
        $purchases = $supplier->purchases()
            ->latest('purchase_date')->latest('id')
            ->paginate(15, ['*'], 'purchases_page')
            ->withQueryString();
        $payments = $supplier->payments()
            ->with('purchase:id,invoice_number')
            ->latest('payment_date')->latest('id')
            ->paginate(15, ['*'], 'payments_page')
            ->withQueryString();

        return view('suppliers.show', compact('supplier', 'purchases', 'payments'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(SaveSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $data = $request->validated();
        $oldOpeningCents = (int) round(((float) $supplier->opening_balance) * 100);
        $newOpeningCents = (int) round(((float) ($data['opening_balance'] ?? 0)) * 100);
        $balanceCents = (int) round(((float) $supplier->balance) * 100);
        $balanceCents = max(0, $balanceCents + ($newOpeningCents - $oldOpeningCents));

        $supplier->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'opening_balance' => number_format($newOpeningCents / 100, 2, '.', ''),
            'balance' => number_format($balanceCents / 100, 2, '.', ''),
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('suppliers.show', $supplier)->with('success', 'تم تحديث بيانات المورد.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'تم حذف المورد.');
    }

    public function storePurchase(SavePurchaseRequest $request, Supplier $supplier): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $supplier): void {
            $locked = Supplier::query()->lockForUpdate()->findOrFail($supplier->id);
            $totalCents = (int) round(((float) $data['total_amount']) * 100);
            $isCash = $data['payment_type'] === 'cash';
            $paidCents = $isCash
                ? $totalCents
                : (int) round(((float) ($data['paid_amount'] ?? 0)) * 100);
            $paidCents = min($paidCents, $totalCents);
            $remainingCents = $totalCents - $paidCents;

            $purchase = Purchase::create([
                'supplier_id' => $locked->id,
                'invoice_number' => $this->uniquePurchaseNumber(),
                'purchase_date' => $data['purchase_date'],
                'payment_type' => $data['payment_type'],
                'total_amount' => number_format($totalCents / 100, 2, '.', ''),
                'paid_amount' => number_format($paidCents / 100, 2, '.', ''),
                'remaining' => number_format($remainingCents / 100, 2, '.', ''),
                'status' => Purchase::statusFromAmounts($paidCents, $totalCents),
                'notes' => $data['notes'] ?? null,
            ]);

            if ($remainingCents > 0) {
                $locked->balance = number_format(
                    ((int) round(((float) $locked->balance) * 100) + $remainingCents) / 100,
                    2,
                    '.',
                    ''
                );
                $locked->save();
            }

            if ($isCash && $paidCents > 0) {
                SupplierPayment::create([
                    'supplier_id' => $locked->id,
                    'purchase_id' => $purchase->id,
                    'receipt_number' => $this->uniqueReceiptNumber(),
                    'amount' => number_format($paidCents / 100, 2, '.', ''),
                    'payment_date' => $data['purchase_date'],
                    'notes' => 'سداد فاتورة كاش '.$purchase->invoice_number,
                ]);
            }
        });

        return redirect()->route('suppliers.show', $supplier)->with('success', 'تم تسجيل فاتورة الشراء.');
    }

    public function storePayment(StoreSupplierPaymentRequest $request, Supplier $supplier): RedirectResponse
    {
        $data = $request->validated();

        try {
            DB::transaction(function () use ($data, $supplier): void {
                $locked = Supplier::query()->lockForUpdate()->findOrFail($supplier->id);
                $amountCents = (int) round(((float) $data['amount']) * 100);
                $balanceCents = (int) round(((float) $locked->balance) * 100);

                if ($amountCents > $balanceCents) {
                    throw ValidationException::withMessages([
                        'amount' => 'المبلغ أكبر من المستحق على المورد ('.number_format($balanceCents / 100, 2).' ج.م).',
                    ]);
                }

                $remainingToApply = $amountCents;
                $purchases = Purchase::query()
                    ->where('supplier_id', $locked->id)
                    ->where('remaining', '>', 0)
                    ->orderBy('purchase_date')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $firstPurchaseId = null;

                foreach ($purchases as $purchase) {
                    if ($remainingToApply <= 0) {
                        break;
                    }

                    $dueCents = (int) round(((float) $purchase->remaining) * 100);
                    $apply = min($dueCents, $remainingToApply);
                    $paidCents = (int) round(((float) $purchase->paid_amount) * 100) + $apply;
                    $dueCents -= $apply;
                    $totalCents = (int) round(((float) $purchase->total_amount) * 100);

                    $purchase->update([
                        'paid_amount' => number_format($paidCents / 100, 2, '.', ''),
                        'remaining' => number_format($dueCents / 100, 2, '.', ''),
                        'status' => Purchase::statusFromAmounts($paidCents, $totalCents),
                    ]);

                    $firstPurchaseId ??= $purchase->id;
                    $remainingToApply -= $apply;
                }

                $locked->balance = number_format(($balanceCents - $amountCents) / 100, 2, '.', '');
                $locked->save();

                SupplierPayment::create([
                    'supplier_id' => $locked->id,
                    'purchase_id' => $firstPurchaseId,
                    'receipt_number' => $this->uniqueReceiptNumber(),
                    'amount' => number_format($amountCents / 100, 2, '.', ''),
                    'payment_date' => $data['payment_date'],
                    'notes' => $data['notes'] ?? null,
                ]);
            });
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()->route('suppliers.show', $supplier)->with('success', 'تم تسجيل سند الصرف وتخفيض الرصيد.');
    }

    private function uniquePurchaseNumber(): string
    {
        do {
            $number = 'PUR-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Purchase::query()->where('invoice_number', $number)->exists());

        return $number;
    }

    private function uniqueReceiptNumber(): string
    {
        do {
            $number = 'PAY-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (SupplierPayment::query()->where('receipt_number', $number)->exists());

        return $number;
    }
}
