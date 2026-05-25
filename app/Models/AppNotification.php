<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AppNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'starts_at',
        'ends_at',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($sub) {
                $sub->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($sub) {
                $sub->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
