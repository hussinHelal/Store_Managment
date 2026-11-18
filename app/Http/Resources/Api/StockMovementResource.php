<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => (int) $this->branch_id,
            'branch_name' => $this->whenLoaded('branch', fn () => $this->branch?->name),
            'product_id' => (int) $this->product_id,
            'product_name' => $this->whenLoaded('product', fn () => $this->product?->name),
            'type' => $this->type,
            'quantity_delta' => (int) $this->quantity_delta,
            'balance_after' => (int) $this->balance_after,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'created_by' => $this->created_by,
            'created_by_name' => $this->whenLoaded('creator', fn () => $this->creator?->name),
            'note' => $this->note,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
