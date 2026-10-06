<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SyncAccountsTest extends TestCase
{
    use RefreshDatabase;

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
    }

    public function test_sync_accounts_is_idempotent_and_hashes_credentials(): void
    {
        $this->artisan('app:sync-accounts')->assertExitCode(0);
        $this->artisan('app:sync-accounts')->assertExitCode(0);

        $this->assertDatabaseCount('users', 2);

        $superadmin = User::where('system_account', 'superadmin')->firstOrFail();
        $admin = User::where('system_account', 'admin')->firstOrFail();

        $this->assertSame('root.user', $superadmin->username);
        $this->assertSame('Root User', $superadmin->name);
        $this->assertTrue(Hash::check('super-secret-pass', $superadmin->password));
        $this->assertTrue($superadmin->hasRole('Superadmin'));
            $this->assertFalse($superadmin->hasAllPermissions(['page.assign-roles.view']));
        $this->assertSame('store.admin', $admin->username);
        $this->assertTrue(Hash::check('admin-secret-pass', $admin->password));
        $this->assertTrue($admin->hasRole('Admin'));
        $adminRole = $admin->roles()->firstOrFail();
        $this->assertFalse($adminRole->permissions()->where('name', 'like', '%assign-roles%')->exists());
    }

    public function test_sync_updates_only_the_two_marked_accounts(): void
    {
        $untouched = User::factory()->create([
            'username' => 'ordinary.staff',
            'name' => 'Ordinary Staff',
            'password' => 'unchanged-password',
        ]);

        $this->artisan('app:sync-accounts')->assertExitCode(0);
        config([
            'accounts.superadmin.username' => 'ROOT.NEW',
            'accounts.superadmin.password' => 'new-super-password',
            'accounts.superadmin.name' => 'New Root',
            'accounts.admin.username' => 'ADMIN.NEW',
            'accounts.admin.password' => 'new-admin-password',
            'accounts.admin.name' => 'New Admin',
        ]);

        $this->artisan('app:sync-accounts')->assertExitCode(0);

        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseHas('users', ['system_account' => 'superadmin', 'username' => 'root.new', 'name' => 'New Root']);
        $this->assertDatabaseHas('users', ['system_account' => 'admin', 'username' => 'admin.new', 'name' => 'New Admin']);
        $this->assertSame('ordinary.staff', $untouched->fresh()->username);
        $this->assertSame('Ordinary Staff', $untouched->fresh()->name);
        $this->assertTrue(Hash::check('unchanged-password', $untouched->fresh()->password));
    }

    public function test_sync_accounts_rejects_missing_duplicate_or_short_credentials(): void
    {
        config(['accounts.superadmin.username' => '']);
        $this->artisan('app:sync-accounts')->expectsOutputToContain('must be configured')->assertExitCode(1);

        config([
            'accounts.superadmin.username' => 'same.account',
            'accounts.admin.username' => 'same.account',
        ]);
        $this->artisan('app:sync-accounts')->expectsOutputToContain('must be different')->assertExitCode(1);

        config([
            'accounts.superadmin.username' => 'root.user',
            'accounts.admin.username' => 'store.admin',
            'accounts.admin.password' => 'short',
        ]);
        $this->artisan('app:sync-accounts')->expectsOutputToContain('at least 10 characters')->assertExitCode(1);
    }
}