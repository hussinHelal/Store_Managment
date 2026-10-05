<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CashierCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Same ability the invoice form uses. The route middleware checks it too.
        return $this->user()?->can('create-invoice') ?? false;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'min:16', 'max:100', 'regex:/^[A-Za-z0-9\-]+$/'],
            'customer' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'received' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'السلة فارغة.',
            'items.min' => 'السلة فارغة.',
            'items.max' => 'السلة تحتوي على عدد كبير جداً من الأصناف.',
            'items.*.quantity.min' => 'الكمية يجب أن تكون 1 على الأقل.',
            'items.*.quantity.integer' => 'الكمية يجب أن تكون رقماً صحيحاً.',
            'received.numeric' => 'المبلغ المستلم غير صالح.',
            'received.decimal' => 'المبلغ المستلم يقبل حتى رقمين بعد الفاصلة.',
            'customer.max' => 'اسم العميل طويل جداً.',
        ];
    }
}
