<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WalletAuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletAuditPageTest extends TestCase
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

    public function test_wallet_audit_renders_transaction_details_in_arabic_and_filters_by_event_group(): void
    {
        WalletAuditLog::query()->forceCreate([
            'user_id' => $this->superadmin->id,
            'user_name' => $this->superadmin->name,
            'action' => 'transaction.created',
            'subject_type' => 'wallet_transaction',
            'subject_id' => 10,
            'meta' => [
                'type' => 'receive',
                'amount' => '1000.00',
                'method' => 'cash',
                'wallet_id' => 2,
                'commission' => '10.00',
            ],
            'ip' => '127.0.0.1',
            'created_at' => now(),
        ]);
        WalletAuditLog::query()->forceCreate([
            'user_id' => $this->superadmin->id,
            'user_name' => $this->superadmin->name,
            'action' => 'damaged.recorded',
            'subject_type' => 'damaged_item',
            'subject_id' => 11,
            'meta' => ['quantity' => 3, 'reason' => 'broken'],
            'ip' => '127.0.0.1',
            'created_at' => now()->subMinute(),
        ]);

        $this->actingAs($this->superadmin, 'web')
            ->get(route('wallets.audit.index'))
            ->assertOk()
            ->assertSee('تصفية حسب نوع السجل')
            ->assertSee('معاملات المحافظ')
            ->assertSee('تسجيل معاملة')
            ->assertSee('استلام على المحفظة')
            ->assertSee('نقدي')
            ->assertSee('1,000.00 ج.م')
            ->assertDontSee('transaction.created')
            ->assertDontSee('wallet_transaction')
            ->assertDontSee('"type"');

        $this->get(route('wallets.audit.index', ['action' => 'transaction.']))
            ->assertOk()
            ->assertSee('تسجيل معاملة')
            ->assertDontSee('تسجيل صنف تالف')
            ->assertSee('value="transaction." selected', false);
    }
}