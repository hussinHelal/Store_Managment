<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class installments extends Model
{
    protected $fillable = [
        'invoice_id',
        'customer',
        'product_id',
        'product_name',
        'product_price',
        'payment_date',
        'next_payment_date',
        'paid_amount',
        'remaining',
        'quantity',
        'status',
        'items',
    ];

    protected $casts = [
        'payment_date'     => 'date',
        'next_payment_date'=> 'date',
        'product_price'    => 'decimal:2',
        'paid_amount'      => 'decimal:2',
        'remaining'        => 'decimal:2',
        'items'            => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }

    public function invoice()
    {
        return $this->belongsTo(invoice::class, 'invoice_id');
    }

    public function getItemNamesAttribute()
    {
        if (!is_array($this->items)) {
            return $this->product_name;
        }

        return collect($this->items)->pluck('name')->implode(', ');
    }

    public function getItemQuantityAttribute()
    {
        if (!is_array($this->items)) {
            return $this->quantity;
        }

        return collect($this->items)->sum('quantity');
    }
}
