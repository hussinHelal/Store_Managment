<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallationAppointment extends Model
{
    public const STATUSES = ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled'];

    protected $fillable = [
        'customer_name', 'phone', 'device_model', 'service_type', 'service_details', 'scheduled_at',
        'duration_minutes', 'technician_id', 'status', 'address', 'notes', 'completed_at', 'created_by',
    ];

    protected $casts = ['scheduled_at' => 'datetime', 'completed_at' => 'datetime'];

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
