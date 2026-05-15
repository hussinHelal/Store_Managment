<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class customers extends Model
{
    protected $fillable = ['name', 'address', 'phone'];

    public function installments()
    {
        return $this->hasMany(installments::class, 'customer_id');
    }
}
