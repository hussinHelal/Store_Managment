<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletCashierPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config([
            'accounts.superadmin.username' => 'root.user',
            'accounts.superadmin.password' => 'super-secret-pass',
            'accounts.superadmin.name' => 'Root User',
            'accounts.admin.username' => 'store.admin',
            'accounts.admin.password' => 'admin-secret-pass',
            'accounts.admin.name' => 'Store Admin',
        ]);
        $this->artisan('app:sync-accounts')->assertExitCode(0);
        $this->superadmin = User::where('system_account', 'superadmin')->firstOrFail();
    }

    public function test_cashier_transaction_and_debt_lists_have_pagination(): void
    {
        $wallet = Wallet::query()->forceCreate([
            'name' => 'محفظة الاختبار',
            'provider' => 'other',
            'identifier' => 'pagination-test-wallet',
            'opening_balance' => '0.00',
            'balance' => '0.00',
        ]);

        foreach (range(1, 26) as $number) {
            $wallet->transactions()->forceCreate([
                'type' => 'send',
                'status' => 'completed',
                'amount' => '10.00',
                'commission' => '0.00',
                'fee' => '0.00',
                'profit' => '0.00',
                'wallet_delta' => '-10.00',
                'cash_delta' => '10.00',
                'balance_after' => '0.00',
                'payment_method' => 'deferred',
                'receivable' => '10.00',
                'customer_name' => 'عميل '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'occurred_at' => now()->subMinutes($number),
                'created_by' => $this->superadmin->id,
                'created_by_name' => $this->superadmin->name,
            ]);
        }

        $this->actingAs($this->superadmin, 'web')
            ->get(route('wallet_cashier.index'))
            ->assertOk()
            ->assertSee('transactions_page=2', false);

        $this->get(route('wallet_cashier.debts'))
            ->assertOk()
            ->assertSee('عرض 1 إلى 20 من 26 عميل')
            ->assertSee('customers_page=2', false);

        $this->get(route('wallet_cashier.debts', ['customers_page' => 2]))
            ->assertOk()
            ->assertSee('عرض 21 إلى 26 من 26 عميل')
            ->assertSee('عميل 26');
    }

    public function test_wallet_cards_paginate_without_changing_global_balance_total(): void
    {
        foreach (range(1, 13) as $number) {
            Wallet::query()->forceCreate([
                'name' => 'محفظة '.$number,
                'provider' => 'other',
                'identifier' => 'wallet-page-'.$number,
                'opening_balance' => '10.00',
                'balance' => '10.00',
            ]);
        }

        $this->actingAs($this->superadmin, 'web')
            ->get(route('wallets.index'))
            ->assertOk()
            ->assertSee('إجمالي أرصدة المحافظ النشطة')
            ->assertSee('130.00')
            ->assertSee('عرض 1 إلى 12 من 13 محفظة')
            ->assertSee('wallets_page=2', false);
    }
}