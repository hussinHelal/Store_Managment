<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => (int) $this->branch_id,
            'branch' => $this->whenLoaded('branch', fn () => ['id' => $this->branch->id, 'name' => $this->branch->name]),
            'product_id' => (int) $this->product_id,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'barcode' => $this->product->barcode,
                'category' => $this->product->category?->name,
            ]),
            'quantity' => (int) $this->quantity,
            'reorder_level' => (int) $this->reorder_level,
            'is_low' => $this->reorder_level > 0 && $this->quantity <= $this->reorder_level,
        ];
    }
}
