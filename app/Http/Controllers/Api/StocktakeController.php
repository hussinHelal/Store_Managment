<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CountStocktakeItemRequest;
use App\Http\Requests\Api\CreateStocktakeRequest;
use App\Http\Resources\Api\StocktakeResource;
use App\Models\Stocktake;
use App\Services\ApiIdempotencyService;
use App\Services\StocktakeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class StocktakeController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'status' => ['nullable', 'in:draft,counting,review,approved,cancelled'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);
        $query = Stocktake::query()->with('branch')
            ->when(isset($filters['branch_id']), fn ($q) => $q->where('branch_id', $filters['branch_id']))
            ->when(isset($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->orderByDesc('id');

        return StocktakeResource::collection($query->paginate($filters['per_page'] ?? 20));
    }

    public function show(Stocktake $stocktake): StocktakeResource
    {
        return new StocktakeResource($stocktake->load(['branch', 'items.product']));
    }

    public function store(CreateStocktakeRequest $request, StocktakeService $service, ApiIdempotencyService $idempotency): JsonResponse
    {
        $data = $request->validated();
        $key = $data['idempotency_key'];

        return $idempotency->execute($request->user(), 'stocktake.start', $key, $data, function () use ($request, $service, $data, $key): array {
            $take = $service->start($data, $request->user(), $key)->load(['branch', 'items.product']);

            return ['status' => 201, 'body' => ['data' => (new StocktakeResource($take))->resolve($request)]];
        });
    }

    public function count(CountStocktakeItemRequest $request, Stocktake $stocktake, int $item, StocktakeService $service, ApiIdempotencyService $idempotency): JsonResponse
    {
        $data = $request->validated();
        $key = $this->key($request);

        return $idempotency->execute($request->user(), 'stocktake.count', $key, ['stocktake_id' => $stocktake->id, 'item_id' => $item] + $data, function () use ($service, $stocktake, $item, $data): array {
            $counted = $service->count($stocktake, $item, (int) $data['counted_qty'], $data['note'] ?? null);

            return ['status' => 200, 'body' => ['data' => [
                'id' => $counted->id,
                'counted_qty' => $counted->counted_qty,
                'counted_at' => $counted->counted_at?->toISOString(),
            ]]];
        });
    }

    public function review(Request $request, Stocktake $stocktake, StocktakeService $service, ApiIdempotencyService $idempotency): JsonResponse
    {
        $key = $this->key($request);

        return $idempotency->execute($request->user(), 'stocktake.review', $key, ['stocktake_id' => $stocktake->id], function () use ($request, $service, $stocktake): array {
            $updated = $service->review($stocktake)->load('branch');

            return ['status' => 200, 'body' => ['data' => (new StocktakeResource($updated))->resolve($request)]];
        });
    }

    public function approve(Request $request, Stocktake $stocktake, StocktakeService $service, ApiIdempotencyService $idempotency): JsonResponse
    {
        abort_unless($request->user()->can('page.stocktake.approve'), 403);
        $key = $this->key($request);

        return $idempotency->execute($request->user(), 'stocktake.approve', $key, ['stocktake_id' => $stocktake->id], function () use ($request, $service, $stocktake): array {
            $updated = $service->approve($stocktake, $request->user())->load(['branch', 'items.product']);

            return ['status' => 200, 'body' => ['data' => (new StocktakeResource($updated))->resolve($request)]];
        });
    }

    public function cancel(Request $request, Stocktake $stocktake, StocktakeService $service, ApiIdempotencyService $idempotency): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:255']]);
        $key = $this->key($request);

        return $idempotency->execute($request->user(), 'stocktake.cancel', $key, ['stocktake_id' => $stocktake->id] + $data, function () use ($request, $service, $stocktake, $data): array {
            $updated = $service->cancel($stocktake, $data['reason'])->load('branch');

            return ['status' => 200, 'body' => ['data' => (new StocktakeResource($updated))->resolve($request)]];
        });
    }

    private function key(Request $request): string
    {
        $key = (string) $request->header('Idempotency-Key');
        if (! preg_match('/^[A-Za-z0-9-]{16,64}$/', $key)) {
            throw ValidationException::withMessages(['Idempotency-Key' => 'أرسل مفتاح تكرار صالحاً لهذه العملية.']);
        }

        return $key;
    }
}
