<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StocktakeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'branch_id' => (int) $this->branch_id,
            'branch_name' => $this->whenLoaded('branch', fn () => $this->branch?->name),
            'scope' => $this->scope,
            'category_id' => $this->category_id,
            'status' => $this->status,
            'snapshot_at' => $this->snapshot_at?->toISOString(),
            'note' => $this->note,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => (int) $item->product_id,
                'product_name' => $item->product?->name,
                'barcode' => $item->product?->barcode,
                'system_qty' => (int) $item->system_qty,
                'counted_qty' => $item->counted_qty,
                'difference' => $item->difference,
                'note' => $item->note,
            ])),
        ];
    }
}
