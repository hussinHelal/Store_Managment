<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierStatementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('page.suppliers.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'status' => ['nullable', 'in:all,open,paid'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ];
    }
}
