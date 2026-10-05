<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('page.suppliers.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'purchase_date' => ['required', 'date'],
            'payment_type' => ['required', 'in:cash,credit'],
            'total_amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999'],
            'paid_amount' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $total = (float) $this->input('total_amount', 0);
            $paid = $this->filled('paid_amount') ? (float) $this->input('paid_amount') : null;
            $type = $this->input('payment_type');

            if ($type === 'cash' && $paid !== null && $paid + 0.0001 < $total) {
                $validator->errors()->add('paid_amount', 'فاتورة الكاش يجب دفعها بالكامل.');
            }

            if ($paid !== null && $paid > $total) {
                $validator->errors()->add('paid_amount', 'المدفوع لا يمكن أن يتجاوز إجمالي الفاتورة.');
            }
        });
    }
}
