<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\category;

class products extends Model
{
    protected $fillable = ['name', 'price', 'quantity','description','stock', 'category_id'];
    
    public function category()
    {
        return $this->belongsTo(category::class, 'category_id');
    }
    
    public function sales()
    {
        return $this->hasMany(sales::class, 'product_id');
    }
    
    public function installments()
    {
        return $this->hasMany(installments::class, 'product_id');
    }
    
}
