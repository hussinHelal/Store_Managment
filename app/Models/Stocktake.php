<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stocktake extends Model
{
    protected $fillable = [
        'number', 'branch_id', 'scope', 'category_id', 'status', 'started_by', 'approved_by',
        'snapshot_at', 'snapshot_movement_id', 'approved_at', 'note', 'idempotency_key',
    ];

    protected function casts(): array
    {
        return ['snapshot_at' => 'datetime', 'approved_at' => 'datetime', 'snapshot_movement_id' => 'integer'];
    }

    public function items()
    {
        return $this->hasMany(StocktakeItem::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function category()
    {
        return $this->belongsTo(category::class, 'category_id');
    }
}
