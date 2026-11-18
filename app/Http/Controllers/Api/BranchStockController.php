<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StockAdjustmentRequest;
use App\Http\Resources\Api\BranchStockResource;
use App\Http\Resources\Api\StockMovementResource;
use App\Models\BranchStock;
use App\Models\StockMovement;
use App\Services\ApiIdempotencyService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class BranchStockController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'availability' => ['nullable', 'in:all,low,out'],
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);
        $query = BranchStock::query()->with(['branch', 'product.category'])
            ->when(isset($filters['branch_id']), fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->when(isset($filters['category_id']), fn ($q) => $q->whereHas('product', fn ($product) => $product->where('category_id', $filters['category_id'])));
        if (($filters['availability'] ?? 'all') === 'out') {
            $query->where('quantity', 0);
        } elseif (($filters['availability'] ?? 'all') === 'low') {
            $query->where('reorder_level', '>', 0)->whereColumn('quantity', '<=', 'reorder_level');
        }
        $term = trim((string) ($filters['q'] ?? ''));
        if ($term !== '') {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $query->whereHas('product', fn ($product) => $product->where('name', 'like', $like)->orWhere('barcode', 'like', $like));
        }

        return BranchStockResource::collection($query->orderBy('branch_id')->orderBy('product_id')->paginate($filters['per_page'] ?? 30));
    }

    public function reorderLevel(Request $request, BranchStock $branchStock): BranchStockResource
    {
        $data = $request->validate(['reorder_level' => ['required', 'integer', 'between:0,1000000']]);
        $updated = DB::transaction(function () use ($branchStock, $data): BranchStock {
            $locked = BranchStock::query()->lockForUpdate()->findOrFail($branchStock->id);
            $locked->update(['reorder_level' => $data['reorder_level']]);

            return $locked->refresh()->load(['branch', 'product.category']);
        }, 3);

        return new BranchStockResource($updated);
    }

    public function adjust(StockAdjustmentRequest $request, StockService $stock, ApiIdempotencyService $idempotency): JsonResponse
    {
        $data = $request->validated();
        $key = $data['idempotency_key'];
        unset($data['idempotency_key']);

        return $idempotency->execute($request->user(), 'stock_movements.adjust', $key, $data, function () use ($request, $stock, $data, $key): array {
            $product = $stock->change(
                (int) $data['product_id'], (int) $data['quantity_delta'], 'manual_adjust', $request->user(),
                $data['reason'], idempotencyKey: $key, branchId: (int) $data['branch_id'],
            );
            $movement = StockMovement::query()->with(['branch', 'product', 'creator'])->where('idempotency_key', $key)->firstOrFail();

            return ['status' => 200, 'body' => [
                'data' => (new StockMovementResource($movement))->resolve($request),
                'product_stock' => (int) $product->stock,
            ]];
        });
    }

    public function movements(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'type' => ['nullable', 'string', 'max:32'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);
        $query = StockMovement::query()->with(['branch', 'product', 'creator'])
            ->when(isset($filters['branch_id']), fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->when(isset($filters['product_id']), fn ($q) => $q->where('product_id', $filters['product_id']))
            ->when(isset($filters['type']), fn ($q) => $q->where('type', $filters['type']))
            ->when(isset($filters['from']), fn ($q) => $q->whereDate('created_at', '>=', $filters['from']))
            ->when(isset($filters['to']), fn ($q) => $q->whereDate('created_at', '<=', $filters['to']))
            ->orderByDesc('id');

        return StockMovementResource::collection($query->paginate($filters['per_page'] ?? 30));
    }
}
