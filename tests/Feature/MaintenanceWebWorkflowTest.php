<?php

namespace Tests\Feature;

use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceWebWorkflowTest extends TestCase
{
    use RefreshDatabase;

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
        $this->admin = User::where('system_account', 'admin')->firstOrFail();
    }

    public function test_maintenance_forms_show_status_and_image_controls_in_arabic(): void
    {
        $maintenance = $this->maintenance();

        $this->actingAs($this->admin, 'web')->get(route('maintenance.create'))
            ->assertOk()->assertSee('إضافة طلب صيانة')->assertSee('name="image"', false)
            ->assertSee('قيد الانتظار')->assertSee('مكتمل')->assertSee('مرفوض');
        $this->get(route('maintenance.edit', $maintenance))
            ->assertOk()->assertSee('name="image"', false)->assertSee('id="status"', false)
            ->assertSee('مرفوض');
    }

    public function test_maintenance_index_shows_a_device_photo_thumbnail_when_present(): void
    {
        $maintenance = $this->maintenance();
        $maintenance->update(['image_path' => 'uploads/maintenance/test-phone.jpg']);

        $this->actingAs($this->admin, 'web')->get(route('maintenance.index'))
            ->assertOk()
            ->assertSee('uploads/maintenance/test-phone.jpg')
            ->assertSee('class="img-thumbnail"', false);
    }

    public function test_status_can_be_changed_repeatedly_and_completion_date_tracks_completed_state(): void
    {
        $maintenance = $this->maintenance();
        $this->actingAs($this->admin, 'web')
            ->put(route('maintenance.repaired', $maintenance), ['status' => 'مرفوض'])
            ->assertRedirect(route('maintenance.index'));
        $this->assertSame('مرفوض', $maintenance->fresh()->status);
        $this->assertNull($maintenance->fresh()->completed_date);

        $this->get(route('maintenance.showRepaired', $maintenance))
            ->assertOk()->assertSee('name="status"', false)->assertSee('مكتمل');
        $this->put(route('maintenance.repaired', $maintenance), ['status' => 'مكتمل'])
            ->assertRedirect(route('maintenance.index'));
        $this->assertSame('مكتمل', $maintenance->fresh()->status);
        $this->assertNotNull($maintenance->fresh()->completed_date);

        $this->put(route('maintenance.repaired', $maintenance), ['status' => 'قيد الانتظار'])
            ->assertRedirect(route('maintenance.index'));
        $this->assertSame('قيد الانتظار', $maintenance->fresh()->status);
        $this->assertNull($maintenance->fresh()->completed_date);
    }

    private function maintenance(): Maintenance
    {
        return Maintenance::query()->create([
            'name' => 'هاتف للاختبار',
            'owner' => 'عميل للاختبار',
            'phone' => '01012345678',
            'address' => 'القاهرة',
            'description' => 'فحص الجهاز',
            'status' => 'قيد الانتظار',
            'requested_date' => now()->toDateString(),
        ]);
    }
}
