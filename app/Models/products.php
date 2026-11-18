<?php

namespace App\Models;

use App\Services\PictureStorageService;
use Illuminate\Database\Eloquent\Model;

class products extends Model
{
    protected $fillable = ['name', 'price', 'cost_price', 'description', 'notes', 'barcode', 'stock', 'category_id', 'image'];

    protected $casts = ['cost_price' => 'decimal:2'];

    public function getImageUrlAttribute(): ?string
    {
        return app(PictureStorageService::class)->productUrl($this, true);
    }

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

    public function invoices()
    {
        return $this->hasMany(invoice::class, 'product_id');
    }

    public function branchStocks()
    {
        return $this->hasMany(BranchStock::class, 'product_id');
    }
}
