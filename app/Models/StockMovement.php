<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class StockMovement extends Model
{
    protected $fillable = [
        'branch_id', 'product_id', 'type', 'quantity_delta', 'balance_after',
        'reference_type', 'reference_id', 'created_by', 'note', 'idempotency_key',
    ];

    protected function casts(): array
    {
        return ['quantity_delta' => 'integer', 'balance_after' => 'integer'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Stock movements are append-only.'));
        static::deleting(fn () => throw new LogicException('Stock movements are append-only.'));
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
