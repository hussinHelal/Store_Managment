<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class installments extends Model
{
    protected $fillable = ['customer', 'amount', 'due_date','payment_data','next_payment_date','payment_date','paid_amount',"paid_at"];

    protected $casts = [
           'due_date'          => 'date',
           'payment_date'      => 'date',
           'next_payment_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(customers::class, 'customer_id');
    }
}
