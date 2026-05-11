<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class installments extends Model
{
    protected $fillable = ['customer_id', 'amount', 'due_date','payment_data','next_payment_date','payment_date'];
}
