<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackedDevice extends Model
{
    public const STATUSES = ['in_stock', 'sold', 'returned', 'repair', 'retired'];

    protected $fillable = ['product_id', 'imei', 'serial_number', 'status', 'customer_name', 'invoice_id', 'notes', 'created_by'];

    public function product(): BelongsTo { return $this->belongsTo(products::class, 'product_id'); }
    public function invoice(): BelongsTo { return $this->belongsTo(invoice::class, 'invoice_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
