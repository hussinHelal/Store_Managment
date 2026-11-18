<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackedDeviceRequest;
use App\Models\products as Product;
use App\Models\TrackedDevice;
use App\Services\TrackedDeviceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TrackedDeviceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(TrackedDevice::STATUSES)],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);
        $term = trim($filters['q'] ?? '');
        $devices = TrackedDevice::query()->with(['product', 'invoice'])
            ->when($term !== '', fn ($q) => $q->where(fn ($inner) => $inner->where('imei', 'like', '%'.$term.'%')->orWhere('serial_number', 'like', '%'.$term.'%')->orWhere('customer_name', 'like', '%'.$term.'%')->orWhereHas('product', fn ($p) => $p->where('name', 'like', '%'.$term.'%'))))
            ->when(isset($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->orderByDesc('id')->paginate($filters['per_page'] ?? 25)->withQueryString();

        return view('devices.index', ['devices' => $devices, 'products' => Product::query()->orderBy('name')->get(['id', 'name']), 'statuses' => TrackedDevice::STATUSES]);
    }

    public function store(TrackedDeviceRequest $request, TrackedDeviceService $service)
    {
        $service->create($request->validated(), $request->user());

        return redirect()->route('devices.index')->with('success', 'تم تسجيل الجهاز بنجاح.');
    }

    public function update(TrackedDeviceRequest $request, TrackedDevice $device, TrackedDeviceService $service)
    {
        $service->update($device, $request->validated());

        return redirect()->route('devices.index')->with('success', 'تم تحديث بيانات الجهاز بنجاح.');
    }

    public function destroy(TrackedDevice $device, TrackedDeviceService $service)
    {
        $service->delete($device);

        return redirect()->route('devices.index')->with('success', 'تم حذف سجل الجهاز.');
    }
}
