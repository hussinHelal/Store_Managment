<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StocktakeItem extends Model
{
    protected $fillable = ['stocktake_id', 'product_id', 'system_qty', 'counted_qty', 'difference', 'counted_at', 'counted_movement_id', 'note'];

    protected function casts(): array
    {
        return [
            'system_qty' => 'integer',
            'counted_qty' => 'integer',
            'difference' => 'integer',
            'counted_at' => 'datetime',
            'counted_movement_id' => 'integer',
        ];
    }

    public function stocktake()
    {
        return $this->belongsTo(Stocktake::class);
    }

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }
}
