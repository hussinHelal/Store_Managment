<?php

namespace App\Http\Resources\Api;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'purchase_date' => $this->purchase_date?->toDateString(),
            'payment_type' => $this->payment_type,
            'total_amount' => Money::decimal(Money::cents($this->total_amount)),
            'paid_amount' => Money::decimal(Money::cents($this->paid_amount)),
            'remaining' => Money::decimal(Money::cents($this->remaining)),
            'status' => $this->status,
            'branch' => $this->whenLoaded('branch', fn () => ['id' => $this->branch?->id, 'name' => $this->branch?->name]),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => (int) $item->quantity,
                'received_quantity' => (int) $item->received_quantity,
                'unit_cost' => Money::decimal(Money::cents($item->unit_cost)),
                'line_total' => Money::decimal(Money::cents($item->line_total)),
                'current_product_cost' => $request->user()?->can('page.products.manage') && $item->product?->cost_price !== null
                    ? Money::decimal(Money::cents($item->product->cost_price)) : null,
                'cost_warning' => $item->product?->cost_price === null
                    || Money::cents($item->product?->cost_price ?? 0) === 0
                    || Money::cents($item->product?->cost_price ?? 0) !== Money::cents($item->unit_cost),
            ])->values()),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
