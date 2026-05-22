<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    protected $fillable = ['name', 'owner','phone','address', 'description', 'status', 'requested_date', 'completed_date'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
    protected $casts = [
        'requested_date' => 'date',
        'completed_date' => 'date',
    ];


}
