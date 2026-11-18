<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrackedDeviceRequest;
use App\Http\Resources\Api\TrackedDeviceResource;
use App\Models\TrackedDevice;
use App\Services\ApiIdempotencyService;
use App\Services\TrackedDeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class TrackedDeviceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', Rule::in(TrackedDevice::STATUSES)], 'per_page' => ['nullable', 'integer', 'between:1,100']]);
        $term = trim($filters['q'] ?? '');
        $query = TrackedDevice::query()->with(['product', 'invoice'])
            ->when($term !== '', fn ($q) => $q->where(fn ($inner) => $inner->where('imei', 'like', '%'.$term.'%')->orWhere('serial_number', 'like', '%'.$term.'%')->orWhere('customer_name', 'like', '%'.$term.'%')->orWhereHas('product', fn ($p) => $p->where('name', 'like', '%'.$term.'%'))))
            ->when(isset($filters['status']), fn ($q) => $q->where('status', $filters['status']))->orderByDesc('id');

        return TrackedDeviceResource::collection($query->paginate($filters['per_page'] ?? 25));
    }

    public function show(Request $request, TrackedDevice $device): TrackedDeviceResource
    {
        return new TrackedDeviceResource($device->load(['product', 'invoice']));
    }

    public function store(TrackedDeviceRequest $request, TrackedDeviceService $service, ApiIdempotencyService $idempotency): JsonResponse
    {
        $data = $request->validated();
        $key = validator(['idempotency_key' => $request->header('Idempotency-Key')], ['idempotency_key' => ['required', 'string', 'min:16', 'max:64', 'regex:/^[A-Za-z0-9-]+$/']])->validate()['idempotency_key'];

        return $idempotency->execute($request->user(), 'devices.create', $key, $data, function () use ($request, $service, $data): array {
            $device = $service->create($data, $request->user())->load('product');

            return ['status' => 201, 'body' => ['data' => (new TrackedDeviceResource($device))->resolve($request)]];
        });
    }

    public function update(TrackedDeviceRequest $request, TrackedDevice $device, TrackedDeviceService $service, ApiIdempotencyService $idempotency): JsonResponse
    {
        $data = $request->validated();
        $key = validator(['idempotency_key' => $request->header('Idempotency-Key')], ['idempotency_key' => ['required', 'string', 'min:16', 'max:64', 'regex:/^[A-Za-z0-9-]+$/']])->validate()['idempotency_key'];

        return $idempotency->execute($request->user(), 'devices.update', $key, ['device_id' => $device->id] + $data, function () use ($request, $service, $device, $data): array {
            $updated = $service->update($device, $data);

            return ['status' => 200, 'body' => ['data' => (new TrackedDeviceResource($updated))->resolve($request)]];
        });
    }

    public function destroy(Request $request, TrackedDevice $device, TrackedDeviceService $service, ApiIdempotencyService $idempotency): JsonResponse
    {
        abort_unless($request->user()->can('page.devices.manage'), 403);
        $key = validator(['idempotency_key' => $request->header('Idempotency-Key')], ['idempotency_key' => ['required', 'string', 'min:16', 'max:64', 'regex:/^[A-Za-z0-9-]+$/']])->validate()['idempotency_key'];

        return $idempotency->execute($request->user(), 'devices.delete', $key, ['device_id' => $device->id], function () use ($service, $device): array {
            $service->delete($device);

            return ['status' => 204, 'body' => []];
        });
    }
}
