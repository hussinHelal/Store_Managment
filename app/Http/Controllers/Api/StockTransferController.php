<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CreateStockTransferRequest;
use App\Http\Resources\Api\StockTransferResource;
use App\Models\StockTransfer;
use App\Services\ApiIdempotencyService;
use App\Services\StockTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class StockTransferController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'status' => ['nullable', 'in:draft,sent,received,cancelled'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);
        $query = StockTransfer::query()->with(['source', 'destination', 'items.product'])
            ->when(isset($filters['branch_id']), fn ($q) => $q->where(fn ($q) => $q
                ->where('from_branch_id', $filters['branch_id'])->orWhere('to_branch_id', $filters['branch_id'])))
            ->when(isset($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->orderByDesc('id');

        return StockTransferResource::collection($query->paginate($filters['per_page'] ?? 20));
    }

    public function store(CreateStockTransferRequest $request, StockTransferService $transfers, ApiIdempotencyService $idempotency): JsonResponse
    {
        $data = $request->validated();
        $key = $data['idempotency_key'];

        return $idempotency->execute($request->user(), 'stock_transfers.create', $key, $data, function () use ($request, $transfers, $data, $key): array {
            $transfer = $transfers->create($data, $request->user(), $key)->load(['source', 'destination', 'items.product']);

            return ['status' => 201, 'body' => ['data' => (new StockTransferResource($transfer))->resolve($request)]];
        });
    }

    public function update(CreateStockTransferRequest $request, StockTransfer $transfer, StockTransferService $transfers, ApiIdempotencyService $idempotency): JsonResponse
    {
        $data = $request->validated();
        $key = $data['idempotency_key'];
        $payload = ['transfer_id' => $transfer->id] + $data;
        unset($payload['idempotency_key']);

        return $idempotency->execute($request->user(), 'stock_transfers.update', $key, $payload, function () use ($request, $transfers, $transfer, $data): array {
            $updated = $transfers->updateDraft($transfer, $data)->load(['source', 'destination', 'items.product']);

            return ['status' => 200, 'body' => ['data' => (new StockTransferResource($updated))->resolve($request)]];
        });
    }

    public function send(Request $request, StockTransfer $transfer, StockTransferService $transfers, ApiIdempotencyService $idempotency): JsonResponse
    {
        $key = $this->key($request);

        return $idempotency->execute($request->user(), 'stock_transfers.send', $key, ['transfer_id' => $transfer->id], function () use ($request, $transfers, $transfer): array {
            $updated = $transfers->send($transfer, $request->user())->load(['source', 'destination', 'items.product']);

            return ['status' => 200, 'body' => ['data' => (new StockTransferResource($updated))->resolve($request)]];
        });
    }

    public function receive(Request $request, StockTransfer $transfer, StockTransferService $transfers, ApiIdempotencyService $idempotency): JsonResponse
    {
        $key = $this->key($request);

        return $idempotency->execute($request->user(), 'stock_transfers.receive', $key, ['transfer_id' => $transfer->id], function () use ($request, $transfers, $transfer): array {
            $updated = $transfers->receive($transfer, $request->user())->load(['source', 'destination', 'items.product']);

            return ['status' => 200, 'body' => ['data' => (new StockTransferResource($updated))->resolve($request)]];
        });
    }

    public function cancel(Request $request, StockTransfer $transfer, StockTransferService $transfers, ApiIdempotencyService $idempotency): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:255']]);
        $key = $this->key($request);

        return $idempotency->execute($request->user(), 'stock_transfers.cancel', $key, ['transfer_id' => $transfer->id] + $data, function () use ($request, $transfers, $transfer, $data): array {
            $updated = $transfers->cancel($transfer, $data['reason'], $request->user())->load(['source', 'destination', 'items.product']);

            return ['status' => 200, 'body' => ['data' => (new StockTransferResource($updated))->resolve($request)]];
        });
    }

    public function destroy(Request $request, StockTransfer $transfer, StockTransferService $transfers, ApiIdempotencyService $idempotency): JsonResponse
    {
        $key = $this->key($request);

        return $idempotency->execute($request->user(), 'stock_transfers.delete', $key, ['transfer_id' => $transfer->id], function () use ($transfers, $transfer): array {
            $transfers->deleteDraft($transfer);

            return ['status' => 204, 'body' => []];
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
