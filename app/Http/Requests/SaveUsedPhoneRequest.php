<?php

namespace App\Http\Requests;

use App\Support\PictureRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveUsedPhoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('page.used_phones.manage') ?? false;
    }

    public function rules(): array
    {
        $phone = $this->route('usedPhone');
        $id = is_object($phone) ? $phone->getKey() : $phone;
        $imeiRules = ['nullable', 'string', 'max:32'];
        $serialRules = ['nullable', 'string', 'max:100'];
        if ($id || ! $this->is('api/*')) {
            $imeiRules[] = Rule::unique('used_phones', 'imei')->ignore($id);
            $serialRules[] = Rule::unique('used_phones', 'serial_number')->ignore($id);
        }

        return [
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:160'],
            'imei' => $imeiRules,
            'serial_number' => $serialRules,
            'condition' => ['required', Rule::in(['excellent', 'good', 'fair', 'poor'])],
            'purchase_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'asking_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'purchase_date' => ['required', 'date'],
            'seller_name' => ['required', 'string', 'max:160'],
            'seller_phone' => ['nullable', 'string', 'max:32'],
            'status' => ['required', Rule::in(['in_stock', 'reserved', 'sold', 'returned'])],
            'image' => PictureRules::upload(),
            'remove_image' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
