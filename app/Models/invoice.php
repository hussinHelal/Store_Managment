<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class invoice extends Model
{
    protected $fillable = ['invoice_number', 'customer', 'description', 'invoice_date', 'total_amount', 'status'];
}
