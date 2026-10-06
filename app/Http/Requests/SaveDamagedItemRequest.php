<?php

namespace App\Http\Requests;

use App\Models\DamagedItem;
use App\Support\Digits;
use App\Support\PictureRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveDamagedItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('page.damaged.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['quantity', 'unit_value'] as $field) {
            if ($this->filled($field)) {
                $merge[$field] = trim((string) Digits::ascii((string) $this->input($field)));
            }
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'reason' => ['required', Rule::in(array_keys(DamagedItem::REASONS))],
            'unit_value' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999'],
            'damaged_on' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
            'image' => PictureRules::upload(),
            'idempotency_key' => ['required', 'string', 'min:16', 'max:64', 'regex:/^[A-Za-z0-9\-]+$/'],
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => 'المنتج',
            'quantity' => 'الكمية',
            'reason' => 'سبب الهالك',
            'unit_value' => 'قيمة الوحدة',
            'damaged_on' => 'تاريخ الهالك',
            'notes' => 'الملاحظات',
            'image' => 'الصورة',
        ];
    }
}
