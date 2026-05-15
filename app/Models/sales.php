<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sales extends Model
{
    

    protected $fillable = [
        'product_id',
        'quantity',
        'total_price',
        'sale_date',
    ];

    public function products()
    {
        return $this->belongsTo(products::class);
    }
    
}
