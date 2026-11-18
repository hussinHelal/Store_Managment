<?php

namespace App\Services;

use App\Models\InstallationAppointment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstallationAppointmentService
{
    public function create(array $data, int $userId): InstallationAppointment
    {
        return DB::transaction(function () use ($data, $userId): InstallationAppointment {
            $this->assertTechnicianSlotAvailable($data);
            $data['created_by'] = $userId;
            $data['completed_at'] = $data['status'] === 'completed' ? now() : null;

            return InstallationAppointment::query()->create($data);
        }, 3);
    }

    public function update(InstallationAppointment $appointment, array $data): InstallationAppointment
    {
        return DB::transaction(function () use ($appointment, $data): InstallationAppointment {
            $locked = InstallationAppointment::query()->lockForUpdate()->findOrFail($appointment->id);
            $this->assertTechnicianSlotAvailable($data, $locked->id);
            $data['completed_at'] = $data['status'] === 'completed' ? ($locked->completed_at ?? now()) : null;
            $locked->update($data);

            return $locked->fresh(['technician']);
        }, 3);
    }

    public function delete(InstallationAppointment $appointment): void
    {
        DB::transaction(fn () => InstallationAppointment::query()->lockForUpdate()->findOrFail($appointment->id)->delete(), 3);
    }

    private function assertTechnicianSlotAvailable(array $data, ?int $exceptId = null): void
    {
        if (empty($data['technician_id']) || ! in_array($data['status'] ?? null, ['scheduled', 'confirmed', 'in_progress'], true)) {
            return;
        }

        // Serialize booking attempts for a technician, then compare actual time ranges.
        User::query()->lockForUpdate()->findOrFail($data['technician_id']);
        $start = Carbon::parse($data['scheduled_at']);
        $end = $start->copy()->addMinutes((int) $data['duration_minutes']);
        $conflict = InstallationAppointment::query()
            ->where('technician_id', $data['technician_id'])
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->where('scheduled_at', '>=', $start->copy()->subMinutes(480))
            ->where('scheduled_at', '<', $end)
            ->get(['id', 'scheduled_at', 'duration_minutes'])
            ->contains(fn (InstallationAppointment $row): bool => $row->scheduled_at->copy()->addMinutes((int) $row->duration_minutes)->gt($start));

        if ($conflict) {
            throw ValidationException::withMessages(['scheduled_at' => 'الفني لديه موعد آخر يتعارض مع هذه الفترة.']);
        }
    }
}
