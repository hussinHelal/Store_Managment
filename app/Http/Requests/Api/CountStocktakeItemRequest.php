<?php

namespace App\Http\Requests\Api;

class CountStocktakeItemRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('page.stocktake.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'counted_qty' => ['required', 'integer', 'between:0,1000000'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
