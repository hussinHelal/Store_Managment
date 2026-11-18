<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_groups_all_existing_new_pages_under_arabic_categories(): void
    {
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
        $user = User::where('system_account', 'superadmin')->firstOrFail();

        $this->actingAs($user, 'web')->get(route('home'))->assertOk()
            ->assertSee('lang="ar"', false)->assertSee('dir="rtl"', false)
            ->assertSee('<details class="sidebar-group"', false)
            ->assertSee('<summary class="sidebar-group-toggle"', false)
            ->assertSee('data-sidebar-group="overview"', false)
            ->assertSee('data-sidebar-group="sales"', false)
            ->assertSee('data-sidebar-group="inventory"', false)
            ->assertSee('data-sidebar-group="service"', false)
            ->assertSee('data-sidebar-group="accounts"', false)
            ->assertSee('data-sidebar-group="administration"', false)
            ->assertSee(route('branch_stock.index'), false)
            ->assertSee(route('stock_movements.index'), false)
            ->assertSee(route('stocktake.index'), false)
            ->assertSee(route('branch_stock.transfers.index'), false)
            ->assertSee(route('devices.index'), false)
            ->assertSee(route('used_phones.index'), false)
            ->assertSee(route('installations.index'), false)
            ->assertSee(route('diagnostics.index'), false);

        $this->actingAs($user, 'web')->get(route('diagnostics.index'))->assertOk()
            ->assertSee('تشخيص الأجهزة')->assertSee('navigator.mediaDevices')
            ->assertSee('id="startDiagnosticScan"', false)
            ->assertSee('window.createBarcodeScanner', false);
    }
}
