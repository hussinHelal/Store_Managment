<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class invoice extends Model
{
    protected $fillable = ['invoice_number', 'customer', 'product_id','paid_amount', 'quantity', 'invoice_date', 'total_amount', 'status'];

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
    public function products()
    {
        return $this->belongsTo(products::class, 'product_id');
    }
    public function products_count()
    {
        return $this->belongsToMany(products::class, 'product_id')->count();
    }
    public function calculate_total_amount()
    {
        return $this->belongsToMany(products::class, 'product_id')->sum('product_price * quantity');
    }
}
