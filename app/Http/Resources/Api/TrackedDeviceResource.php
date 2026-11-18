<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackedDeviceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => (int) $this->product_id,
            'product_name' => $this->whenLoaded('product', fn () => $this->product->name),
            'imei' => $this->imei,
            'serial_number' => $this->serial_number,
            'status' => $this->status,
            'customer_name' => $this->customer_name,
            'invoice_id' => $this->invoice_id,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
