<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    public const PROVIDERS = [
        'vodafone_cash' => 'فودافون كاش',
        'instapay' => 'إنستا باي',
        'orange_cash' => 'أورنج كاش',
        'etisalat_cash' => 'اتصالات كاش',
        'we_pay' => 'WE Pay',
        'other' => 'أخرى',
    ];

    /**
     * "balance" and "opening_balance" are deliberately NOT mass assignable.
     * The balance only changes through WalletLedgerService.
     */
    protected $fillable = [
        'name', 'provider', 'identifier', 'holder_name',
        'per_transaction_limit', 'daily_send_limit', 'daily_receive_limit',
        'monthly_send_limit', 'monthly_receive_limit', 'warn_at_percent',
        'default_commission_percent', 'default_commission_min',
        'default_fee_percent', 'default_fee_min', 'default_fee_max',
        'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'warn_at_percent' => 'integer',
            'opening_balance' => 'decimal:2',
            'balance' => 'decimal:2',
            'per_transaction_limit' => 'decimal:2',
            'daily_send_limit' => 'decimal:2',
            'daily_receive_limit' => 'decimal:2',
            'monthly_send_limit' => 'decimal:2',
            'monthly_receive_limit' => 'decimal:2',
            'default_commission_percent' => 'decimal:2',
            'default_commission_min' => 'decimal:2',
            'default_fee_percent' => 'decimal:2',
            'default_fee_min' => 'decimal:2',
            'default_fee_max' => 'decimal:2',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function providerLabel(): string
    {
        return self::PROVIDERS[$this->provider] ?? (string) $this->provider;
    }

    /** A limit in cents, or null when it is empty / zero (= unlimited). */
    public function limitCents(string $column): ?int
    {
        $cents = Money::cents($this->{$column});

        return $cents > 0 ? $cents : null;
    }
}
