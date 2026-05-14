<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\products;

class category extends Model
{
    protected $fillable = ['name'];

    public function products()
    {
        return $this->hasMany(products::class, 'category_id');
    }
}
