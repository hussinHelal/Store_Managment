<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class installments extends Model
{
    protected $fillable = ['customer', 'product_id', 'product_price', 'product_name', 'next_payment_date', 'payment_date', 'paid_amount', 'remaining', 'quantity'];

    protected $casts = [
           'payment_date'      => 'date',
           'next_payment_date' => 'date',
    ];

    // public function customer()
    // {
    //     return $this->hasOne(customers::class, 'id');
    // }
    
    public function product()
    {
        return $this->belongsTo(products::class, 'product_id', 'id');
    }
}
