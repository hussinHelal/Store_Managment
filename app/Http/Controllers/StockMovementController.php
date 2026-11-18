<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\products;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'type' => ['nullable', 'string', 'max:32'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $movements = StockMovement::query()->with(['branch', 'product', 'creator'])
            ->when(isset($filters['branch_id']), fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->when(isset($filters['product_id']), fn ($q) => $q->where('product_id', $filters['product_id']))
            ->when(isset($filters['type']), fn ($q) => $q->where('type', $filters['type']))
            ->when(isset($filters['from']), fn ($q) => $q->whereDate('created_at', '>=', $filters['from']))
            ->when(isset($filters['to']), fn ($q) => $q->whereDate('created_at', '<=', $filters['to']))
            ->orderByDesc('id')->paginate(30)->withQueryString();
        $branches = Branch::query()->orderBy('name')->get(['id', 'name']);
        $products = products::query()->orderBy('name')->get(['id', 'name']);
        $types = StockMovement::query()->select('type')->distinct()->orderBy('type')->pluck('type');

        return view('stock_movements.index', compact('movements', 'branches', 'products', 'types', 'filters'));
    }
}
