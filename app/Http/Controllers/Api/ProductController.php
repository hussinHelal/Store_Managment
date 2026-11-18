<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CreateProductRequest;
use App\Http\Requests\Api\DeleteProductRequest;
use App\Http\Requests\Api\ProductIndexRequest;
use App\Http\Requests\Api\UpdateProductImageRequest;
use App\Http\Requests\Api\UpdateProductRequest;
use App\Http\Resources\Api\ProductResource;
use App\Models\Branch;
use App\Models\installments as Installment;
use App\Models\invoice as Invoice;
use App\Models\products;
use App\Models\StockMovement;
use App\Models\TrackedDevice;
use App\Services\ApiIdempotencyService;
use App\Services\CurrentBranch;
use App\Services\PictureStorageService;
use App\Services\StockService;
use App\Support\ProductCostSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductController
{
    public function index(ProductIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $columns = ['id', 'name', 'price', 'description', 'notes', 'barcode', 'stock', 'category_id', 'image', 'updated_at'];
        if (ProductCostSchema::available()) {
            $columns[] = 'cost_price';
        }
        $query = products::query()
            ->select($columns)
            ->with('category:id,name')
            ->when(isset($filters['category_id']), fn ($query) => $query->where('category_id', $filters['category_id']))
            ->when(isset($filters['in_stock']), function ($query) use ($filters): void {
                $operator = filter_var($filters['in_stock'], FILTER_VALIDATE_BOOLEAN) ? '>' : '<=';
                $query->where('stock', $operator, 0);
            });

        $term = trim((string) ($filters['q'] ?? ''));
        if ($term !== '') {
            $search = '%'.addcslashes($term, '%_\\').'%';
            $query->where(fn ($query) => $query
                ->where('name', 'like', $search)
                ->orWhere('barcode', 'like', $search));
        }

        return ProductResource::collection(
            $query->orderByDesc('id')->paginate($filters['per_page'] ?? 20)
        );
    }

    public function show(products $product): ProductResource
    {
        return new ProductResource($product->load('category'));
    }

    public function store(CreateProductRequest $request, ApiIdempotencyService $idempotency, StockService $stock, CurrentBranch $currentBranch): JsonResponse
    {
        $data = $request->validated();
        ProductCostSchema::prepare($data, $request);
        $key = $data['idempotency_key'];
        unset($data['idempotency_key']);

        return $idempotency->execute(
            $request->user(),
            'products.create',
            $key,
            $data,
            function () use ($request, $data, $stock, $key): array {
                if (! empty($data['barcode']) && products::query()->where('barcode', $data['barcode'])->exists()) {
                    throw ValidationException::withMessages(['barcode' => 'قيمة الباركود مستخدمة من قبل.']);
                }

                $openingQuantity = (int) $data['stock'];
                $data['stock'] = 0;
                $product = products::query()->create($data);
                foreach (Branch::query()->where('is_active', true)->pluck('id') as $branchId) {
                    $stock->initializeBranch((int) $product->id, (int) $branchId);
                }
                if ($openingQuantity > 0) {
                    $stock->increase(
                        (int) $product->id, $openingQuantity, 'opening', $request->user(),
                        'رصيد افتتاحي عند إنشاء المنتج', $product, 'product-create:'.$key,
                    );
                }
                $product->refresh()->load('category');

                return [
                    'status' => 201,
                    'body' => ['data' => (new ProductResource($product))->resolve($request)],
                ];
            }
        );
    }

    public function update(
        UpdateProductRequest $request,
        products $product,
        ApiIdempotencyService $idempotency,
        StockService $stock,
    ): JsonResponse {
        $data = $request->validated();
        ProductCostSchema::prepare($data, $request);
        $key = $data['idempotency_key'];
        unset($data['idempotency_key']);

        return $idempotency->execute(
            $request->user(),
            'products.update',
            $key,
            ['product_id' => $product->id] + $data,
            function () use ($request, $product, $data, $stock, $key): array {
                $updated = DB::transaction(function () use ($request, $product, $data, $stock, $key): products {
                    $locked = products::query()->lockForUpdate()->findOrFail($product->id);
                    $targetStock = (int) $data['stock'];
                    $reason = trim((string) ($data['stock_adjustment_reason'] ?? ''));
                    unset($data['stock'], $data['stock_adjustment_reason']);
                    $delta = $targetStock - (int) $locked->stock;
                    $locked->update($data);
                    if ($delta !== 0) {
                        $stock->setAggregate(
                            (int) $locked->id, $targetStock, $request->user(),
                            $reason !== '' ? $reason : 'تعديل المخزون من تحديث المنتج',
                            $locked, 'product-update:'.$key,
                        );
                    }

                    return $locked->refresh()->load('category');
                }, 3);

                return ['status' => 200, 'body' => ['data' => (new ProductResource($updated))->resolve($request)]];
            }
        );
    }

    public function updateImage(UpdateProductImageRequest $request, products $product, PictureStorageService $pictures): JsonResponse
    {
        $newPath = $pictures->store($request->file('image'), 'products');
        $oldPath = $product->image;

        try {
            $updated = DB::transaction(function () use ($product, $newPath): products {
                $locked = products::query()->lockForUpdate()->findOrFail($product->id);
                $locked->update(['image' => $newPath]);

                return $locked->refresh()->load('category');
            }, 3);
        } catch (\Throwable $exception) {
            $pictures->delete($newPath, 'products');
            throw $exception;
        }

        if ($oldPath !== $newPath) {
            $pictures->delete($oldPath, 'products');
        }

        return response()->json(['data' => (new ProductResource($updated))->resolve($request)]);
    }

    public function destroy(
        DeleteProductRequest $request,
        products $product,
        ApiIdempotencyService $idempotency,
        PictureStorageService $pictures,
    ): JsonResponse {
        $key = $request->validated('idempotency_key');

        return $idempotency->execute(
            $request->user(),
            'products.delete',
            $key,
            ['product_id' => $product->id],
            function () use ($product, $pictures): array {
                $image = $product->image;
                DB::transaction(function () use ($product): void {
                    $locked = products::query()->lockForUpdate()->findOrFail($product->id);
                    if ($this->hasTransactionHistory($locked)) {
                        throw ValidationException::withMessages([
                            'product' => 'لا يمكن حذف المنتج لوجود سجل مبيعات أو مخزون مرتبط.',
                        ]);
                    }
                    $locked->delete();
                }, 3);
                $pictures->delete($image, 'products');

                return ['status' => 204, 'body' => []];
            }
        );
    }

    private function hasTransactionHistory(products $product): bool
    {
        $line = '%"product_id":'.(int) $product->id.',%';

        return $product->invoices()->exists()
            || $product->installments()->exists()
            || $product->sales()->exists()
            || Invoice::query()->where('items', 'like', $line)->exists()
            || Installment::query()->where('items', 'like', $line)->exists()
            || TrackedDevice::query()->where('product_id', $product->id)->exists()
            || StockMovement::query()->where('product_id', $product->id)->exists();
    }
}
