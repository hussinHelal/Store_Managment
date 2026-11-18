<?php

namespace Tests\Feature;

use App\Models\InstallationAppointment;
use App\Models\User;
use App\Services\InstallationAppointmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InstallationAppointmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_appointment_crud_renders_arabic_fields_and_checks_technician_conflicts(): void
    {
        $manager = User::factory()->create(['is_active' => true]);
        $manager->givePermissionTo([
            Permission::findOrCreate('page.installations.view', 'web'),
            Permission::findOrCreate('page.installations.manage', 'web'),
        ]);
        $technician = User::factory()->create(['is_active' => true, 'name' => 'Technician']);
        $payload = [
            'customer_name' => 'Customer', 'phone' => '01012345678', 'device_model' => 'Router X',
            'service_type' => 'إعداد شبكة', 'service_details' => 'اختبار الاتصال',
            'scheduled_at' => '2026-11-20T10:00', 'duration_minutes' => 60, 'technician_id' => $technician->id,
            'status' => 'scheduled', 'address' => 'القاهرة', 'notes' => 'اتصل قبل الوصول',
        ];

        $this->actingAs($manager)->get('/installation-appointments')->assertOk()
            ->assertSee('مواعيد التركيب')->assertSee('name="service_details"', false)
            ->assertSee('name="duration_minutes"', false)->assertSee('name="technician_id"', false)
            ->assertSee('name="notes"', false);
        $this->actingAs($manager)->post('/installation-appointments', $payload)
            ->assertRedirect(route('installations.index'));
        $appointment = InstallationAppointment::query()->firstOrFail();
        $this->assertSame($technician->id, $appointment->technician_id);

        $overlap = array_replace($payload, ['scheduled_at' => '2026-11-20T10:30']);
        $this->actingAs($manager)->post('/installation-appointments', $overlap)
            ->assertSessionHasErrors('scheduled_at');
        $updated = array_replace($payload, ['scheduled_at' => '2026-11-20T12:00', 'status' => 'completed']);
        $this->actingAs($manager)->put('/installation-appointments/'.$appointment->id, $updated)
            ->assertRedirect(route('installations.index'));
        $this->assertNotNull($appointment->fresh()->completed_at);
        $this->actingAs($manager)->delete('/installation-appointments/'.$appointment->id)
            ->assertRedirect(route('installations.index'));
        $this->assertDatabaseMissing('installation_appointments', ['id' => $appointment->id]);
    }

    public function test_api_appointment_mutations_are_permission_gated_idempotent_and_searchable(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo([
            Permission::findOrCreate('page.installations.view', 'web'),
            Permission::findOrCreate('page.installations.manage', 'web'),
        ]);
        $token = $user->createToken('appointments', ['api:me', 'page.installations.view', 'page.installations.manage'])->plainTextToken;
        $payload = [
            'customer_name' => 'API Customer', 'phone' => '01012345678', 'device_model' => 'Phone A',
            'service_type' => 'تركيب تطبيق', 'scheduled_at' => '2026-11-21T12:00:00+02:00',
            'duration_minutes' => 30, 'status' => 'confirmed',
        ];
        $this->withToken($token)->postJson('/api/v1/installation-appointments', $payload)->assertUnprocessable();
        $key = (string) Str::uuid();
        $response = $this->withToken($token)->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/installation-appointments', $payload)->assertCreated()
            ->assertJsonPath('data.customer_name', 'API Customer');
        $id = $response->json('data.id');
        $this->withToken($token)->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/installation-appointments', $payload)->assertCreated()->assertJsonPath('data.id', $id);
        $this->withToken($token)->getJson('/api/v1/installation-appointments?search=API%20Customer')
            ->assertOk()->assertJsonPath('data.0.id', $id);
        $this->withToken($token)->withHeader('Idempotency-Key', (string) Str::uuid())
            ->putJson('/api/v1/installation-appointments/'.$id, array_replace($payload, ['status' => 'in_progress']))
            ->assertOk()->assertJsonPath('data.status', 'in_progress');
        $this->withToken($token)->withHeader('Idempotency-Key', (string) Str::uuid())
            ->deleteJson('/api/v1/installation-appointments/'.$id)->assertOk();
        $this->assertDatabaseMissing('installation_appointments', ['id' => $id]);
    }

    public function test_scheduled_appointments_cannot_overlap_for_same_technician(): void
    {
        $technician = User::factory()->create(['is_active' => true]);
        $service = app(InstallationAppointmentService::class);
        $payload = [
            'customer_name' => 'Customer', 'phone' => '01012345678', 'device_model' => 'Router',
            'service_type' => 'Setup', 'scheduled_at' => '2026-11-22 10:00:00', 'duration_minutes' => 60,
            'technician_id' => $technician->id, 'status' => 'scheduled',
        ];
        $service->create($payload, $technician->id);

        $this->expectException(ValidationException::class);
        $service->create(array_replace($payload, ['scheduled_at' => '2026-11-22 10:45:00']), $technician->id);
    }
}
