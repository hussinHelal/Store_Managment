<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockTransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'from_branch_id' => (int) $this->from_branch_id,
            'from_branch' => $this->whenLoaded('source', fn () => $this->source->name),
            'to_branch_id' => (int) $this->to_branch_id,
            'to_branch' => $this->whenLoaded('destination', fn () => $this->destination->name),
            'status' => $this->status,
            'note' => $this->note,
            'sent_at' => $this->sent_at?->toISOString(),
            'received_at' => $this->received_at?->toISOString(),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'product_id' => (int) $item->product_id,
                'product_name' => $item->product?->name,
                'quantity' => (int) $item->quantity,
            ])),
        ];
    }
}
