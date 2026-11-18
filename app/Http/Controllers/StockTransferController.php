<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStockTransferRequest;
use App\Models\Branch;
use App\Models\products;
use App\Models\StockTransfer;
use App\Services\StockTransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function index(): View
    {
        $transfers = StockTransfer::query()->with(['source', 'destination', 'items.product'])
            ->orderByDesc('id')->paginate(20)->withQueryString();
        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $products = products::query()->orderBy('name')->get(['id', 'name', 'barcode', 'stock']);

        return view('branch_stock.transfers', compact('transfers', 'branches', 'products'));
    }

    public function store(CreateStockTransferRequest $request, StockTransferService $transfers): RedirectResponse
    {
        $data = $request->validated();
        $transfers->create($data, $request->user(), $data['idempotency_key'] ?? (string) Str::uuid());

        return back()->with('success', 'تم إنشاء مسودة التحويل.');
    }

    public function update(CreateStockTransferRequest $request, StockTransfer $transfer, StockTransferService $transfers): RedirectResponse
    {
        $transfers->updateDraft($transfer, $request->safe()->except('idempotency_key'));

        return back()->with('success', 'تم تحديث مسودة التحويل.');
    }

    public function send(Request $request, StockTransfer $transfer, StockTransferService $transfers): RedirectResponse
    {
        $transfers->send($transfer, $request->user());

        return back()->with('success', 'تم إرسال التحويل وخصم الكمية من مخزون المصدر.');
    }

    public function receive(Request $request, StockTransfer $transfer, StockTransferService $transfers): RedirectResponse
    {
        $transfers->receive($transfer, $request->user());

        return back()->with('success', 'تم استلام التحويل وإضافة الكمية إلى الفرع.');
    }

    public function cancel(Request $request, StockTransfer $transfer, StockTransferService $transfers): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:255']]);
        $transfers->cancel($transfer, $data['reason'], $request->user());

        return back()->with('success', 'تم إلغاء التحويل وتسجيل الحركة التعويضية.');
    }

    public function destroy(StockTransfer $transfer, StockTransferService $transfers): RedirectResponse
    {
        $transfers->deleteDraft($transfer);

        return back()->with('success', 'تم حذف مسودة التحويل.');
    }
}
