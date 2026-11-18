<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateStocktakeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('page.stocktake.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'scope' => ['required', Rule::in(['all', 'category'])],
            'category_id' => ['required_if:scope,category', 'nullable', 'integer', 'exists:categories,id'],
            'note' => ['nullable', 'string', 'max:500'],
            'idempotency_key' => ['nullable', 'string', 'min:16', 'max:64', 'regex:/^[A-Za-z0-9-]+$/'],
        ];
    }
}
