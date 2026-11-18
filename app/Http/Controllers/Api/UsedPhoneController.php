<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveUsedPhoneRequest;
use App\Http\Resources\Api\UsedPhoneResource;
use App\Models\UsedPhone;
use App\Services\ApiIdempotencyService;
use App\Services\PictureStorageService;
use App\Services\UsedPhoneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsedPhoneController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:in_stock,reserved,sold,returned'],
            'condition' => ['nullable', 'in:excellent,good,fair,poor'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $search = trim($filters['search'] ?? '');
        $phones = UsedPhone::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('brand', 'like', "%{$search}%")->orWhere('model', 'like', "%{$search}%")
                ->orWhere('imei', 'like', "%{$search}%")->orWhere('seller_name', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['condition'] ?? null, fn ($query, $condition) => $query->where('condition', $condition))
            ->latest('id')->paginate($filters['per_page'] ?? 20)->withQueryString();

        return UsedPhoneResource::collection($phones);
    }

    public function store(SaveUsedPhoneRequest $request, UsedPhoneService $service, PictureStorageService $pictures, ApiIdempotencyService $idempotency): JsonResponse
    {
        $data = $request->validated();
        $image = $request->file('image');
        unset($data['image']);
        if ($image) {
            $data['image_sha256'] = hash_file('sha256', $image->getRealPath());
        }
        $key = validator(['key' => $request->header('Idempotency-Key')], ['key' => ['required', 'string', 'min:16', 'max:64', 'regex:/^[A-Za-z0-9-]+$/']])->validate()['key'];

        return $idempotency->execute($request->user(), 'used_phones.create', $key, $data, function () use ($request, $service, $pictures, $image): array {
            $path = $image ? $pictures->store($image, 'used-phones') : null;
            try {
                $phone = $service->create($request->validated(), (int) $request->user()->id, $path);
            } catch (\Throwable $exception) {
                $pictures->delete($path, 'used-phones');
                throw $exception;
            }

            return ['status' => 201, 'body' => ['data' => (new UsedPhoneResource($phone))->resolve($request)]];
        });
    }

    public function update(SaveUsedPhoneRequest $request, UsedPhone $usedPhone, UsedPhoneService $service, PictureStorageService $pictures, ApiIdempotencyService $idempotency): JsonResponse
    {
        $data = $request->validated();
        $image = $request->file('image');
        unset($data['image']);
        if ($image) {
            $data['image_sha256'] = hash_file('sha256', $image->getRealPath());
        }
        $key = validator(['key' => $request->header('Idempotency-Key')], ['key' => ['required', 'string', 'min:16', 'max:64', 'regex:/^[A-Za-z0-9-]+$/']])->validate()['key'];

        return $idempotency->execute($request->user(), 'used_phones.update', $key, ['used_phone_id' => $usedPhone->id] + $data, function () use ($request, $service, $pictures, $usedPhone, $image): array {
            $newPath = $image ? $pictures->store($image, 'used-phones') : null;
            try {
                [$phone, $oldPath] = $service->update($usedPhone, $request->validated(), $newPath);
            } catch (\Throwable $exception) {
                $pictures->delete($newPath, 'used-phones');
                throw $exception;
            }
            if ($oldPath) {
                DB::afterCommit(fn () => $pictures->delete($oldPath, 'used-phones'));
            }

            return ['status' => 200, 'body' => ['data' => (new UsedPhoneResource($phone))->resolve($request)]];
        });
    }

    public function destroy(Request $request, UsedPhone $usedPhone, UsedPhoneService $service, PictureStorageService $pictures, ApiIdempotencyService $idempotency): JsonResponse
    {
        $key = validator(['key' => $request->header('Idempotency-Key')], ['key' => ['required', 'string', 'min:16', 'max:64', 'regex:/^[A-Za-z0-9-]+$/']])->validate()['key'];

        return $idempotency->execute($request->user(), 'used_phones.delete', $key, ['used_phone_id' => $usedPhone->id], function () use ($usedPhone, $service, $pictures): array {
            $path = $service->delete($usedPhone);
            if ($path) {
                DB::afterCommit(fn () => $pictures->delete($path, 'used-phones'));
            }

            return ['status' => 200, 'body' => ['message' => 'تم حذف الجهاز.']];
        });
    }
}
