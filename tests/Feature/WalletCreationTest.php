<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_wallet_name_and_identifier_are_allowed(): void
    {
        $user = $this->createSuperadmin();
        $attributes = [
            'name' => 'Main wallet',
            'provider' => 'other',
            'identifier' => '01012345678',
            'warn_at_percent' => '80',
        ];

        Wallet::query()->forceCreate($attributes + [
            'opening_balance' => 0,
            'balance' => 0,
        ]);

        $this->actingAs($user, 'web')
            ->post(route('wallets.store'), $attributes)
            ->assertRedirect(route('wallets.index'));

        $this->assertSame(2, Wallet::query()->where('name', 'Main wallet')->count());
        $this->assertSame(2, Wallet::query()->where('identifier', '01012345678')->count());
    }

    public function test_wallet_can_be_created_with_blank_form_fields(): void
    {
        $this->actingAs($this->createSuperadmin(), 'web')
            ->post(route('wallets.store'), [
                'name' => '',
                'provider' => '',
                'identifier' => '',
                'holder_name' => '',
                'opening_balance' => '',
                'warn_at_percent' => '',
                'default_commission_percent' => '',
                'default_commission_min' => '',
                'default_fee_percent' => '',
                'default_fee_min' => '',
                'default_fee_max' => '',
                'notes' => '',
            ])
            ->assertRedirect(route('wallets.index'));

        $wallet = Wallet::query()->firstOrFail();
        $this->assertNull($wallet->name);
        $this->assertNull($wallet->provider);
        $this->assertNull($wallet->identifier);
        $this->assertNull($wallet->warn_at_percent);
        $this->assertNull($wallet->default_commission_percent);
        $this->assertSame('0.00', $wallet->balance);
    }

    public function test_wallet_balance_can_be_set_from_the_edit_form_with_a_reason(): void
    {
        $user = $this->createSuperadmin();
        $wallet = Wallet::query()->forceCreate([
            'name' => 'Main wallet',
            'provider' => 'other',
            'identifier' => '01012345678',
            'opening_balance' => 100,
            'balance' => 100,
        ]);

        $this->actingAs($user, 'web')
            ->put(route('wallets.update', $wallet), [
                'is_active' => '1',
                'balance' => '175',
                'balance_reason' => 'Provider balance correction',
            ])
            ->assertRedirect(route('wallets.index'));

        $this->assertSame('175.00', $wallet->fresh()->balance);
        $adjustment = WalletTransaction::query()->where('wallet_id', $wallet->id)->firstOrFail();
        $this->assertSame('adjustment', $adjustment->type);
        $this->assertSame('75.00', $adjustment->wallet_delta);
        $this->assertSame('Provider balance correction', $adjustment->notes);
    }

    public function test_wallet_balance_edit_requires_a_reason(): void
    {
        $user = $this->createSuperadmin();
        $wallet = Wallet::query()->forceCreate([
            'name' => 'Main wallet',
            'provider' => 'other',
            'identifier' => '01012345678',
            'opening_balance' => 100,
            'balance' => 100,
        ]);

        $this->actingAs($user, 'web')
            ->put(route('wallets.update', $wallet), ['balance' => '175', 'is_active' => '1'])
            ->assertSessionHasErrors('balance_reason');

        $this->assertSame('100.00', $wallet->fresh()->balance);
    }

    private function createSuperadmin(): User
    {
        config([
            'accounts.superadmin.username' => 'root.user',
            'accounts.superadmin.password' => 'super-secret-pass',
            'accounts.superadmin.name' => 'Root User',
            'accounts.admin.username' => 'store.admin',
            'accounts.admin.password' => 'admin-secret-pass',
            'accounts.admin.name' => 'Store Admin',
        ]);

        $this->artisan('app:sync-accounts')->assertExitCode(0);

        return User::query()->where('system_account', 'superadmin')->firstOrFail();
    }
}
