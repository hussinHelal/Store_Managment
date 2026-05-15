<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class payments extends Model
{
   
    protected $fillable = ['invoice_id', 'amount', 'payment_date', 'payment_method'];

    public function invoice()
    {
        return $this->belongsTo(invoice::class, 'invoice_id');
    }
    public function products()
    {
        return $this->belongsToMany(products::class, 'invoice_products', 'invoice_id', 'product_id');
    }
}
