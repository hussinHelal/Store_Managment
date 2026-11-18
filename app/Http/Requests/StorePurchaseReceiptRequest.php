<?php

namespace App\Http\Requests;

use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePurchaseReceiptRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('idempotency_key') && $this->hasHeader('Idempotency-Key')) {
            $this->merge(['idempotency_key' => $this->header('Idempotency-Key')]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can('page.suppliers.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'idempotency_key' => ['required', 'string', 'min:16', 'max:64', 'regex:/^[A-Za-z0-9-]+$/'],
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')->where('is_active', true)],
            'purchase_date' => ['required', 'date'],
            'payment_type' => ['required', Rule::in(['cash', 'credit'])],
            'paid_amount' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.product_name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'between:1,1000000'],
            'items.*.unit_cost' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            foreach ($this->input('items', []) as $index => $item) {
                if (empty($item['product_id']) && trim((string) ($item['product_name'] ?? '')) === '') {
                    $validator->errors()->add("items.$index.product_name", 'اكتب اسم المنتج أو اختر منتجاً من القائمة.');
                }
            }
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $totalCents = 0;
            foreach ($this->input('items', []) as $item) {
                $totalCents += Money::cents($item['unit_cost']) * (int) $item['quantity'];
            }
            if ($totalCents <= 0 || $totalCents > 999_999_999_999) {
                $validator->errors()->add('items', 'إجمالي الفاتورة يجب أن يكون بين 0.01 و9,999,999,999.99 ج.م.');

                return;
            }

            $paid = Money::cents($this->input('paid_amount', 0));
            if ($paid > $totalCents) {
                $validator->errors()->add('paid_amount', 'المبلغ المدفوع لا يمكن أن يتجاوز إجمالي الفاتورة.');
            }
            if ($this->input('payment_type') === 'cash' && $paid !== $totalCents) {
                $validator->errors()->add('paid_amount', 'فاتورة الدفع النقدي يجب سدادها بالكامل.');
            }
        });
    }
}
