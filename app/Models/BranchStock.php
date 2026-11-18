<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchStock extends Model
{
    protected $table = 'branch_stock';

    protected $fillable = ['branch_id', 'product_id', 'quantity', 'reorder_level'];

    protected function casts(): array
    {
        return ['quantity' => 'integer', 'reorder_level' => 'integer'];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }
}
