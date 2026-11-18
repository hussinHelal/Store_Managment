<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class SalesReportRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $today = now('Africa/Cairo')->toDateString();
        $this->merge([
            'from' => $this->input('from', now('Africa/Cairo')->startOfMonth()->toDateString()),
            'to' => $this->input('to', $today),
            'group_by' => $this->input('group_by', 'day'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('page.sales_reports.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'cashier_id' => ['nullable', 'integer', 'exists:users,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'payment_status' => ['nullable', 'in:paid,partial,unpaid'],
            'group_by' => ['required', 'in:day,week,month'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('from') || $validator->errors()->has('to')) {
                return;
            }

            $from = Carbon::parse($this->string('from')->toString(), 'Africa/Cairo');
            $to = Carbon::parse($this->string('to')->toString(), 'Africa/Cairo');
            if ($from->diffInDays($to) > 366) {
                $validator->errors()->add('to', 'يجب ألا تتجاوز فترة التقرير 366 يوماً.');
            }
        }];
    }
}
