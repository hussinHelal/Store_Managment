<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupPageArabicTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_and_import_page_is_arabic_and_keeps_import_manage_gated(): void
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
        $admin = User::where('system_account', 'admin')->firstOrFail();

        $this->actingAs($admin, 'web')->get(route('backup.index'))
            ->assertOk()->assertSee('النسخ الاحتياطي')->assertSee('استيراد نسخة احتياطية')
            ->assertSee('اختر الصفحات')->assertSee('تنزيل النسخة الاحتياطية')
            ->assertSee('استيراد الصفحات المحددة')
            ->assertDontSee('Database backup')->assertDontSee('Download ZIP backup');
    }
}
