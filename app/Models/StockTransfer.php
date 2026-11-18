<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = [
        'number', 'from_branch_id', 'to_branch_id', 'status', 'created_by', 'sent_by',
        'received_by', 'sent_at', 'received_at', 'note', 'idempotency_key',
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime', 'received_at' => 'datetime'];
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function source()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function destination()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }
}
