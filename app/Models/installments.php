<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class installments extends Model
{
    protected $fillable = [
            'customer', 'product_id', 'product_name', 'product_price',
            'payment_date', 'next_payment_date', 'paid_amount', 
            'remaining', 'quantity','status'
        ];
    
        protected $casts = [
            'payment_date'     => 'date',
            'next_payment_date'=> 'date',
            'product_price'    => 'decimal:2',
            'paid_amount'      => 'decimal:2',
            'remaining'        => 'decimal:2',
        ];
    
        public function product()
        {
            return $this->belongsTo(products::class, 'product_id'); // Use singular
        }
}
