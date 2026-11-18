<?php

namespace App\Http\Resources\Api;

use App\Models\UsedPhone;
use App\Services\PictureStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsedPhoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var UsedPhone $phone */
        $phone = $this->resource;

        return [
            'id' => $phone->id,
            'brand' => $phone->brand,
            'model' => $phone->model,
            'imei' => $phone->imei,
            'serial_number' => $phone->serial_number,
            'condition' => $phone->condition,
            'purchase_price' => $phone->purchase_price,
            'asking_price' => $phone->asking_price,
            'purchase_date' => $phone->purchase_date?->toDateString(),
            'seller_name' => $phone->seller_name,
            'seller_phone' => $phone->seller_phone,
            'status' => $phone->status,
            'image_url' => $phone->image_path ? app(PictureStorageService::class)->url($phone->image_path, 'used-phones') : null,
            'notes' => $phone->notes,
            'created_at' => $phone->created_at?->toISOString(),
            'updated_at' => $phone->updated_at?->toISOString(),
        ];
    }
}
