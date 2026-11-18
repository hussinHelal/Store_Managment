<?php

namespace App\Http\Requests;

use App\Models\TrackedDevice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrackedDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('page.devices.manage') ?? false;
    }

    public function rules(): array
    {
        $device = $this->route('device');
        $id = $device instanceof TrackedDevice ? $device->id : null;
        $imeiRules = ['nullable', 'string', 'max:32'];
        $serialRules = ['nullable', 'string', 'max:100'];
        if ($id) {
            $imeiRules[] = Rule::unique('tracked_devices', 'imei')->ignore($id);
            $serialRules[] = Rule::unique('tracked_devices', 'serial_number')->ignore($id);
        }

        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'imei' => $imeiRules,
            'serial_number' => $serialRules,
            'status' => ['required', Rule::in(TrackedDevice::STATUSES)],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'invoice_id' => ['nullable', 'integer', 'exists:invoices,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
