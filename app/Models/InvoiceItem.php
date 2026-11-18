<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'unit_cost',
        'cost_estimated',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'cost_estimated' => 'boolean',
        'line_total' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(invoice::class, 'invoice_id');
    }

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }
}
