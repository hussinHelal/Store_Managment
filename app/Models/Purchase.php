<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id',
        'invoice_number',
        'purchase_date',
        'payment_type',
        'total_amount',
        'paid_amount',
        'remaining',
        'status',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'paid' => 'مدفوعة',
            'partial' => 'جزئية',
            default => 'آجل',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'paid' => 'text-bg-success',
            'partial' => 'text-bg-warning',
            default => 'text-bg-danger',
        };
    }

    public static function statusFromAmounts(int $paidCents, int $totalCents): string
    {
        if ($paidCents >= $totalCents) {
            return 'paid';
        }

        return $paidCents > 0 ? 'partial' : 'unpaid';
    }
}
