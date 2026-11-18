<?php

namespace App\Services;

use App\Models\TrackedDevice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrackedDeviceService
{
    public function create(array $data, User $user): TrackedDevice
    {
        return DB::transaction(function () use ($data, $user): TrackedDevice {
            foreach (['imei', 'serial_number'] as $field) {
                if (! empty($data[$field]) && TrackedDevice::query()->where($field, $data[$field])->exists()) {
                    throw ValidationException::withMessages([$field => 'Ø±Ù‚Ù… Ø§Ù„Ø¬Ù‡Ø§Ø² Ù…Ø³Ø¬Ù„ Ø¨Ø§Ù„ÙØ¹Ù„.']);
                }
            }

            return TrackedDevice::query()->create($data + ['created_by' => $user->id]);
        }, 3);
    }

    public function update(TrackedDevice $device, array $data): TrackedDevice
    {
        return DB::transaction(function () use ($device, $data): TrackedDevice {
            $locked = TrackedDevice::query()->lockForUpdate()->findOrFail($device->id);
            if ($locked->status === 'sold' && (($data['product_id'] ?? $locked->product_id) != $locked->product_id || ($data['imei'] ?? $locked->imei) !== $locked->imei || ($data['serial_number'] ?? $locked->serial_number) !== $locked->serial_number)) {
                throw ValidationException::withMessages(['device' => 'Ù„Ø§ ÙŠÙ…ÙƒÙ† ØªØºÙŠÙŠØ± Ù‡ÙˆÙŠØ© Ø¬Ù‡Ø§Ø² Ù…Ø¨Ø§Ø¹.']);
            }
            foreach (['imei', 'serial_number'] as $field) {
                if (! empty($data[$field]) && TrackedDevice::query()->where($field, $data[$field])->whereKeyNot($locked->id)->exists()) {
                    throw ValidationException::withMessages([$field => 'Ø±Ù‚Ù… Ø§Ù„Ø¬Ù‡Ø§Ø² Ù…Ø³Ø¬Ù„ Ø¨Ø§Ù„ÙØ¹Ù„.']);
                }
            }
            $locked->update($data);

            return $locked->refresh()->load(['product', 'invoice']);
        }, 3);
    }

    public function delete(TrackedDevice $device): void
    {
        DB::transaction(function () use ($device): void {
            $locked = TrackedDevice::query()->lockForUpdate()->findOrFail($device->id);
            if ($locked->status === 'sold' || $locked->invoice_id) {
                throw ValidationException::withMessages(['device' => 'Ù„Ø§ ÙŠÙ…ÙƒÙ† Ø­Ø°Ù Ø¬Ù‡Ø§Ø² Ù…Ø±ØªØ¨Ø· Ø¨Ø¹Ù…Ù„ÙŠØ© Ø¨ÙŠØ¹.']);
            }
            $locked->delete();
        }, 3);
    }
}
