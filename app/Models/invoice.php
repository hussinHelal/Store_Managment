<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer',
        'product_id',
        'paid_amount',
        'quantity',
        'invoice_date',
        'total_amount',
        'status',
        'product_price',
        'items',
    ];

    protected $casts = [
        'items'        => 'array',
        'invoice_date' => 'date',
        'total_amount' => 'decimal:2',
        'product_price'=> 'decimal:2',
        'paid_amount'  => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(payments::class);
    }

    public function payments_count()
    {
        return $this->hasMany(payments::class)->count();
    }

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }

    public function products_count()
    {
        return $this->product()->count();
    }

    public function getItemNamesAttribute()
    {
        if (!is_array($this->items)) {
            return $this->product?->name ?? '';
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
