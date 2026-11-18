<?php

namespace App\Http\Requests;

use App\Models\InstallationAppointment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveInstallationAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('page.installations.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:160'],
            'phone' => ['required', 'string', 'max:32'],
            'device_model' => ['required', 'string', 'max:160'],
            'service_type' => ['required', 'string', 'max:120'],
            'service_details' => ['nullable', 'string', 'max:4000'],
            'scheduled_at' => ['required', 'date', 'after_or_equal:today'],
            'duration_minutes' => ['required', 'integer', 'between:15,480'],
            'technician_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
            'status' => ['required', Rule::in(InstallationAppointment::STATUSES)],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
