<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\category;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BranchStockController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'availability' => ['nullable', 'in:all,low,out'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $query = BranchStock::query()->with(['branch', 'product.category'])
            ->whereHas('branch', fn ($branch) => $branch->where('is_active', true))
            ->when(isset($filters['branch_id']), fn ($stock) => $stock->where('branch_id', $filters['branch_id']))
            ->when(isset($filters['category_id']), fn ($stock) => $stock->whereHas('product', fn ($product) => $product->where('category_id', $filters['category_id'])));
        if (($filters['availability'] ?? 'all') === 'out') {
            $query->where('quantity', 0);
        } elseif (($filters['availability'] ?? 'all') === 'low') {
            $query->whereColumn('quantity', '<=', 'reorder_level')->where('reorder_level', '>', 0)->where('quantity', '>', 0);
        }
        $term = trim((string) ($filters['search'] ?? ''));
        if ($term !== '') {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $query->whereHas('product', fn ($product) => $product->where('name', 'like', $like)->orWhere('barcode', 'like', $like));
        }

        $stocks = $query->orderBy('branch_id')->orderBy('product_id')->paginate(30)->withQueryString();
        $branches = Branch::query()->orderByDesc('is_main')->orderBy('name')->get();
        $categories = category::query()->orderBy('name')->get(['id', 'name']);

        return view('branch_stock.index', compact('stocks', 'branches', 'categories', 'filters'));
    }

    public function adjust(Request $request, StockService $stock): RedirectResponse
    {
        $data = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity_delta' => ['required', 'integer', 'between:-1000000,1000000', 'not_in:0'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:64', 'regex:/^[A-Za-z0-9-]+$/'],
        ]);
        $branch = Branch::query()->whereKey($data['branch_id'])->where('is_active', true)->firstOrFail();
        $stock->change(
            (int) $data['product_id'],
            (int) $data['quantity_delta'],
            'manual_adjust',
            $request->user(),
            $data['reason'],
            idempotencyKey: $data['idempotency_key'],
            branchId: (int) $branch->id,
        );

        return redirect()->route('branch_stock.index', $request->only('branch_id', 'category_id', 'availability', 'search'))
            ->with('success', 'تم تسجيل تسوية المخزون.');
    }

    public function updateReorderLevel(Request $request, BranchStock $branchStock): RedirectResponse
    {
        $data = $request->validate(['reorder_level' => ['required', 'integer', 'between:0,1000000']]);
        DB::transaction(function () use ($branchStock, $data): void {
            $locked = BranchStock::query()->lockForUpdate()->findOrFail($branchStock->id);
            $locked->update(['reorder_level' => $data['reorder_level']]);
        }, 3);

        return back()->with('success', 'تم تحديث حد إعادة الطلب.');
    }
}
