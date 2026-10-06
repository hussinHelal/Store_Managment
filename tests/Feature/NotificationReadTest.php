<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_tap_marks_read_without_redirecting(): void
    {
        Permission::create(['name' => 'page.notifications.view', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'Notification reader', 'guard_name' => 'web']);
        $role->givePermissionTo('page.notifications.view');
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);
        $notification = AppNotification::create([
            'title' => 'تنبيه اختبار',
            'message' => 'رسالة اختبار',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user, 'web')
            ->get(route('notifications.list'))
            ->assertOk()
            ->assertSee('data-notification-read');

        $this->actingAs($user, 'web')
            ->postJson(route('notifications.markRead', $notification))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('message', 'تم وضع الإشعار كمقروء.');

        $this->assertDatabaseHas('notification_reads', [
            'user_id' => $user->id,
            'app_notification_id' => $notification->id,
        ]);
    }

    public function test_notification_list_has_working_pagination_links(): void
    {
        Permission::create(['name' => 'page.notifications.view', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'Notification pager', 'guard_name' => 'web']);
        $role->givePermissionTo('page.notifications.view');
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        foreach (range(1, 21) as $number) {
            AppNotification::create([
                'title' => 'تنبيه '.$number,
                'message' => 'رسالة '.$number,
                'is_active' => true,
                'created_by' => $user->id,
            ]);
        }

        $this->actingAs($user, 'web')
            ->get(route('notifications.list'))
            ->assertOk()
            ->assertSee('عرض 1 إلى 20 من 21 سجل')
            ->assertSee('page=2', false);
    }
}
