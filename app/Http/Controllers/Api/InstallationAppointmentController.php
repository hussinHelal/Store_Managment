<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveInstallationAppointmentRequest;
use App\Http\Resources\Api\InstallationAppointmentResource;
use App\Models\InstallationAppointment;
use App\Services\ApiIdempotencyService;
use App\Services\InstallationAppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstallationAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(InstallationAppointment::STATUSES)],
            'date' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);
        $term = trim($filters['search'] ?? '');
        $records = InstallationAppointment::query()->with('technician:id,name')
            ->when($term !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('customer_name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('device_model', 'like', "%{$term}%")))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('scheduled_at', $date))
            ->orderByDesc('scheduled_at')->paginate($filters['per_page'] ?? 20);

        return InstallationAppointmentResource::collection($records);
    }

    public function store(SaveInstallationAppointmentRequest $request, InstallationAppointmentService $service, ApiIdempotencyService $idempotency): JsonResponse
    {
        $data = $request->validated();
        $key = $this->key($request);

        return $idempotency->execute($request->user(), 'installations.create', $key, $data, function () use ($request, $service, $data): array {
            $record = $service->create($data, (int) $request->user()->id)->load('technician');

            return ['status' => 201, 'body' => ['data' => (new InstallationAppointmentResource($record))->resolve($request)]];
        });
    }

    public function update(SaveInstallationAppointmentRequest $request, InstallationAppointment $appointment, InstallationAppointmentService $service, ApiIdempotencyService $idempotency): JsonResponse
    {
        $data = $request->validated();
        $key = $this->key($request);

        return $idempotency->execute($request->user(), 'installations.update', $key, ['appointment_id' => $appointment->id] + $data, function () use ($request, $service, $appointment, $data): array {
            $record = $service->update($appointment, $data);

            return ['status' => 200, 'body' => ['data' => (new InstallationAppointmentResource($record))->resolve($request)]];
        });
    }

    public function destroy(Request $request, InstallationAppointment $appointment, InstallationAppointmentService $service, ApiIdempotencyService $idempotency): JsonResponse
    {
        $key = $this->key($request);

        return $idempotency->execute($request->user(), 'installations.delete', $key, ['appointment_id' => $appointment->id], function () use ($service, $appointment): array {
            $service->delete($appointment);

            return ['status' => 200, 'body' => ['message' => 'تم حذف الموعد.']];
        });
    }

    private function key(Request $request): string
    {
        return validator(['key' => $request->header('Idempotency-Key')], [
            'key' => ['required', 'string', 'min:16', 'max:64', 'regex:/^[A-Za-z0-9-]+$/'],
        ])->validate()['key'];
    }
}
