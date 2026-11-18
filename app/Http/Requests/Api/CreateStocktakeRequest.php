<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class CreateStocktakeRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['idempotency_key' => $this->header('Idempotency-Key')]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('page.stocktake.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'idempotency_key' => ['required', 'string', 'min:16', 'max:64', 'regex:/^[A-Za-z0-9-]+$/'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'scope' => ['required', Rule::in(['all', 'category'])],
            'category_id' => ['required_if:scope,category', 'nullable', 'integer', 'exists:categories,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
