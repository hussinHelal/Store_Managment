<?php

namespace App\Http\Requests;

use App\Models\Wallet;
use App\Support\Digits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('page.wallets.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $merge = [
            'identifier' => Digits::identifier((string) $this->input('identifier')),
            'is_active' => $this->boolean('is_active'),
        ];

        // Accept Arabic-Indic digits in the number fields.
        foreach ([
            'opening_balance', 'per_transaction_limit', 'daily_send_limit', 'daily_receive_limit',
            'monthly_send_limit', 'monthly_receive_limit', 'default_commission_percent',
            'default_commission_min', 'default_fee_percent', 'default_fee_min', 'default_fee_max',
        ] as $field) {
            if ($this->filled($field)) {
                $merge[$field] = trim((string) Digits::ascii((string) $this->input($field)));
            }
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        $wallet = $this->route('wallet');
        $provider = (string) $this->input('provider');
        $money = ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:999999999'];

        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'provider' => ['required', Rule::in(array_keys(Wallet::PROVIDERS))],
            'identifier' => [
                'required', 'string', 'max:64',
                function (string $attribute, mixed $value, \Closure $fail) use ($provider): void {
                    $value = (string) $value;
                    $valid = Digits::isEgyptianMobile($value)
                        || ($provider === 'instapay' && Digits::isInstapayAddress($value));

                    if (! $valid) {
                        $fail($provider === 'instapay'
                            ? 'اكتب رقم موبايل صحيح أو عنوان إنستا باي (مثل name@instapay).'
                            : 'اكتب رقم موبايل مصري صحيح (11 رقماً يبدأ بـ 010 أو 011 أو 012 أو 015).');
                    }
                },
                Rule::unique('wallets', 'identifier')->where('provider', $provider)->ignore($wallet?->id),
            ],
            'holder_name' => ['nullable', 'string', 'max:100'],
            'per_transaction_limit' => $money,
            'daily_send_limit' => $money,
            'daily_receive_limit' => $money,
            'monthly_send_limit' => $money,
            'monthly_receive_limit' => $money,
            'warn_at_percent' => ['required', 'integer', 'between:1,100'],
            'default_commission_percent' => ['nullable', 'numeric', 'decimal:0,2', 'between:0,100'],
            'default_commission_min' => $money,
            'default_fee_percent' => ['nullable', 'numeric', 'decimal:0,2', 'between:0,100'],
            'default_fee_min' => $money,
            'default_fee_max' => $money,
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        // The opening balance is set once, when the wallet is created. After that
        // the balance only moves through the ledger (transactions or adjustments).
        if (! $wallet) {
            $rules['opening_balance'] = $money;
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name' => 'اسم المحفظة',
            'provider' => 'نوع المحفظة',
            'identifier' => 'رقم المحفظة',
            'holder_name' => 'اسم صاحب المحفظة',
            'opening_balance' => 'الرصيد الافتتاحي',
            'per_transaction_limit' => 'حد العملية الواحدة',
            'daily_send_limit' => 'حد التحويل اليومي',
            'daily_receive_limit' => 'حد الاستلام اليومي',
            'monthly_send_limit' => 'حد التحويل الشهري',
            'monthly_receive_limit' => 'حد الاستلام الشهري',
            'warn_at_percent' => 'نسبة التنبيه',
            'default_commission_percent' => 'نسبة العمولة',
            'default_commission_min' => 'أقل عمولة',
            'default_fee_percent' => 'نسبة رسوم المزود',
            'default_fee_min' => 'أقل رسوم',
            'default_fee_max' => 'أقصى رسوم',
            'notes' => 'ملاحظات',
        ];
    }
}
