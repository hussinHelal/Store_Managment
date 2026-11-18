<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsedPhone extends Model
{
    protected $fillable = [
        'brand', 'model', 'imei', 'serial_number', 'condition', 'purchase_price', 'asking_price',
        'purchase_date', 'seller_name', 'seller_phone', 'status', 'image_path', 'notes', 'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'asking_price' => 'decimal:2',
    ];
}
