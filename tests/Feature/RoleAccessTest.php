<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected User $admin;

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
        $this->admin = User::where('system_account', 'admin')->firstOrFail();
        Route::get('/api/testing/products', fn () => response()->json(['ok' => true]))
            ->middleware(['auth:sanctum', 'page.access'])
            ->name('products.api');
    }

    public function test_only_superadmin_can_open_assign_roles(): void
    {
        $this->actingAs($this->superadmin, 'web')->get('/roles')->assertOk();
        $this->actingAs($this->admin, 'web')->get('/roles')->assertForbidden();

        $normal = $this->staffUser('unassigned.staff');
        $this->actingAs($normal, 'web')->get('/roles')->assertForbidden();
    }

    public function test_role_table_is_paginated(): void
    {
        foreach (range(1, 14) as $number) {
            Role::create(['name' => 'Pagination role '.$number, 'guard_name' => 'web']);
        }

        $this->actingAs($this->superadmin, 'web')
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSee('عرض 1 إلى 15 من 16 دور')
            ->assertSee('roles_page=2', false);
    }

    public function test_legacy_superadmin_role_value_does_not_grant_the_global_bypass(): void
    {
        $legacyUser = User::factory()->create([
            'username' => 'legacy.root',
            'role' => 'superadmin',
        ]);

        $this->assertFalse($legacyUser->isSuperAdmin());
        $this->actingAs($legacyUser, 'web')->get('/roles')->assertForbidden();
    }

    public function test_staff_view_permission_allows_listing_but_not_mutation(): void
    {
        $viewerRole = Role::create(['name' => 'Staff viewer', 'guard_name' => 'web']);
        $viewerRole->givePermissionTo('page.staff.view');
        $viewer = $this->staffUser('staff.viewer', $viewerRole);

        $this->actingAs($viewer, 'web')->get('/users')->assertOk();
        $this->actingAs($viewer, 'web')->get('/users/create')->assertForbidden();
        $this->actingAs($this->admin, 'web')->get('/users/create')->assertOk();
        $this->actingAs($this->admin, 'web')->get('/users')->assertSee('إضافة موظف جديد');
    }

    public function test_superadmin_creates_staff_account_with_email_and_hashed_password(): void
    {
        $role = Role::create(['name' => 'Cashier', 'guard_name' => 'web']);

        $this->actingAs($this->superadmin, 'web')->post(route('users.store'), [
            'username' => 'new.cashier',
            'name' => 'موظف جديد',
            'email' => 'new.cashier@example.test',
            'role_id' => $role->id,
            'password' => 'cashier-secret-123',
            'password_confirmation' => 'cashier-secret-123',
            'is_active' => '1',
        ])->assertRedirect(route('users.index'));

        $user = User::where('email', 'new.cashier@example.test')->firstOrFail();
        $this->assertSame('new.cashier', $user->username);
        $this->assertTrue(Hash::check('cashier-secret-123', $user->getAuthPassword()));
        $this->assertTrue($user->hasRole($role));
    }

    public function test_nested_cashier_routes_use_invoice_permissions_and_keep_create_gate(): void
    {
        $managerRole = Role::create(['name' => 'POS manager', 'guard_name' => 'web']);
        $managerRole->givePermissionTo('page.invoices.manage');
        $manager = $this->staffUser('pos.manager', $managerRole);

        $viewerRole = Role::create(['name' => 'POS viewer', 'guard_name' => 'web']);
        $viewerRole->givePermissionTo('page.invoices.view');
        $viewer = $this->staffUser('pos.viewer', $viewerRole);

        $this->assertFalse($viewer->can('page.invoices.manage'));
        $this->assertFalse($viewer->can('create-invoice'));

        $this->actingAs($manager, 'web')->get(route('cashier.index'))
            ->assertOk()
            ->assertSee('scanCameraButton')
            ->assertSee('cameraScanModal')
            ->assertSee('cancelCameraScan')
            ->assertSee('window.createBarcodeScanner')
            ->assertSee('الكاميرا تعمل. قرّب الباركود')
            ->assertHeader('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');
        $this->actingAs($viewer, 'web')->get(route('cashier.index'))->assertForbidden();
    }

    public function test_role_manage_permission_implies_view_and_cannot_grant_assign_roles(): void
    {
        $this->actingAs($this->superadmin, 'web')->get(route('roles.create'))
            ->assertOk()
            ->assertSee('عرض شاشة كاشير المحافظ والديون.')
            ->assertSee('تسجيل المعاملات وتحصيل المبالغ الآجلة.')
            ->assertSee('عرض سجلات الهالك والتقرير.');

        $this->actingAs($this->superadmin, 'web')->post('/roles', [
            'name' => 'Inventory manager',
            'permissions' => [
                'products' => ['manage' => '1'],
                'wallets' => ['view' => '1'],
                'wallet_cashier' => ['manage' => '1'],
                'damaged' => ['view' => '1'],
            ],
        ])->assertRedirect(route('roles.index'));

        $role = Role::where('name', 'Inventory manager')->firstOrFail();

        $this->assertTrue($role->hasPermissionTo('page.products.manage'));
        $this->assertTrue($role->hasPermissionTo('page.products.view'));
        $this->assertTrue($role->hasPermissionTo('page.wallets.view'));
        $this->assertTrue($role->hasPermissionTo('page.wallet_cashier.manage'));
        $this->assertTrue($role->hasPermissionTo('page.wallet_cashier.view'));
        $this->assertTrue($role->hasPermissionTo('page.damaged.view'));
        $this->assertFalse($role->permissions()->where('name', 'like', '%assign-roles%')->exists());
    }

    public function test_custom_view_and_manage_permissions_are_enforced_on_page_routes(): void
    {
        $viewerRole = Role::create(['name' => 'Category viewer', 'guard_name' => 'web']);
        $viewerRole->givePermissionTo('page.categories.view');
        $viewer = $this->staffUser('category.viewer', $viewerRole);

        $this->actingAs($viewer, 'web')->get(route('categories.index'))->assertOk();
        $this->actingAs($viewer, 'web')->post(route('categories.store'), ['name' => 'Blocked category'])->assertForbidden();

        $managerRole = Role::create(['name' => 'Category manager', 'guard_name' => 'web']);
        $managerRole->givePermissionTo('page.categories.manage');
        $manager = $this->staffUser('category.manager', $managerRole);

    $this->assertTrue($manager->can('page.categories.manage'));
    $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($manager)->allows('create-category'));
        $this->actingAs($manager, 'web')->get(route('categories.index'))->assertOk();
        $this->actingAs($manager, 'web')->post(route('categories.store'), ['name' => 'Allowed category'])
            ->assertRedirect(route('categories.index'));
    }

    public function test_sanctum_token_ability_and_role_permission_are_both_required(): void
    {
        $role = Role::create(['name' => 'API product viewer', 'guard_name' => 'web']);
        $role->givePermissionTo('page.products.view');
        $user = $this->staffUser('api.viewer', $role);
        $allowedToken = $user->createToken('read-products', ['page.products.view']);

        $this->withToken($allowedToken->plainTextToken)->getJson('/api/testing/products')->assertOk();

        $inadequateToken = $user->createToken('wrong-ability', ['user:read']);
        $this->assertFalse($inadequateToken->accessToken->can('page.products.view'));
        $this->withToken($inadequateToken->plainTextToken)->getJson('/api/testing/products')->assertForbidden();

        $unassigned = $this->staffUser('api.unassigned');
        $overbroadToken = $unassigned->createToken('overbroad', ['page.products.view']);
        $this->assertFalse($unassigned->can('page.products.view'));
        $this->withToken($overbroadToken->plainTextToken)->getJson('/api/testing/products')->assertForbidden();
    }

    public function test_role_change_revokes_existing_tokens_and_cannot_target_system_accounts(): void
    {
        $staff = $this->staffUser('token.staff');
        $token = $staff->createToken('test-token', ['page.staff.view']);
        $role = Role::where('name', 'Admin')->firstOrFail();

        $this->actingAs($this->superadmin, 'web')->post(route('roles.users.assign', $staff), [
            'role_id' => $role->id,
        ])->assertRedirect(route('roles.index'));

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);

        $this->actingAs($this->superadmin, 'web')->post(route('roles.users.assign', $this->admin), [
            'role_id' => $role->id,
        ])->assertForbidden();
    }

    private function staffUser(string $username, ?Role $role = null): User
    {
        $user = User::factory()->create([
            'username' => $username,
            'role' => 'cashier',
            'is_active' => true,
        ]);

        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }
}
