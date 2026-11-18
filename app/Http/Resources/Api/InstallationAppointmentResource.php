<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstallationAppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            'phone' => $this->phone,
            'device_model' => $this->device_model,
            'service_type' => $this->service_type,
            'service_details' => $this->service_details,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'duration_minutes' => (int) $this->duration_minutes,
            'technician_id' => $this->technician_id,
            'technician_name' => $this->technician?->name,
            'status' => $this->status,
            'address' => $this->address,
            'notes' => $this->when($request->user()?->can('page.installations.manage'), $this->notes),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
