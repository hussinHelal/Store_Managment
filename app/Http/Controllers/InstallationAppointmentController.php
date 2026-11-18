<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveInstallationAppointmentRequest;
use App\Models\InstallationAppointment;
use App\Models\User;
use App\Services\InstallationAppointmentService;
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
        ]);
        $search = trim($filters['search'] ?? '');
        $appointments = InstallationAppointment::query()->with('technician:id,name')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('customer_name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('device_model', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('scheduled_at', $date))
            ->orderByDesc('scheduled_at')->paginate(15)->withQueryString();
        $technicians = User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('installation_appointments.index', compact('appointments', 'technicians', 'filters'));
    }

    public function create()
    {
        return redirect()->route('installations.index', ['create' => 1]);
    }

    public function store(SaveInstallationAppointmentRequest $request, InstallationAppointmentService $service)
    {
        $service->create($request->validated(), (int) $request->user()->id);

        return redirect()->route('installations.index')->with('success', 'تم حجز موعد التركيب بنجاح.');
    }

    public function edit(InstallationAppointment $appointment)
    {
        return redirect()->route('installations.index', ['edit' => $appointment->id]);
    }

    public function update(SaveInstallationAppointmentRequest $request, InstallationAppointment $appointment, InstallationAppointmentService $service)
    {
        $service->update($appointment, $request->validated());

        return redirect()->route('installations.index')->with('success', 'تم تحديث موعد التركيب.');
    }

    public function destroy(InstallationAppointment $appointment, InstallationAppointmentService $service)
    {
        $service->delete($appointment);

        return redirect()->route('installations.index')->with('success', 'تم حذف الموعد.');
    }
}
