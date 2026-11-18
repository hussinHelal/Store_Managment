<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'code', 'address', 'phone', 'is_main', 'is_active'];

    protected function casts(): array
    {
        return ['is_main' => 'boolean', 'is_active' => 'boolean'];
    }

    public function stockRows()
    {
        return $this->hasMany(BranchStock::class);
    }

    public function fromTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'from_branch_id');
    }

    public function toTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'to_branch_id');
    }
}
