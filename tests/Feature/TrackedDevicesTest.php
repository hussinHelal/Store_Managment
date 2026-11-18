<?php

namespace Tests\Feature;

use App\Models\products;
use App\Models\User;
use App\Services\TrackedDeviceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TrackedDevicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_create_update_and_delete_are_validated_and_persisted(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $product = products::query()->create(['name' => 'Phone', 'price' => '100.00', 'stock' => 2]);
        $service = app(TrackedDeviceService::class);
        $device = $service->create(['product_id' => $product->id, 'imei' => '111222333444555', 'status' => 'in_stock'], $user);
        $this->assertSame('in_stock', $device->status);
        $this->assertSame($user->id, $device->created_by);
        $this->assertSame(2, (int) $product->fresh()->stock);

        $updated = $service->update($device, ['product_id' => $product->id, 'imei' => '111222333444556', 'status' => 'repair']);
        $this->assertSame('repair', $updated->status);
        $this->assertSame('111222333444556', $updated->imei);
        $service->delete($updated);
        $this->assertDatabaseMissing('tracked_devices', ['id' => $device->id]);
    }

    public function test_sold_device_identity_is_immutable_and_cannot_be_deleted(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $product = products::query()->create(['name' => 'Phone', 'price' => '100.00', 'stock' => 1]);
        $device = app(TrackedDeviceService::class)->create(['product_id' => $product->id, 'imei' => '12345', 'status' => 'sold'], $user);
        try {
            app(TrackedDeviceService::class)->update($device, ['product_id' => $product->id, 'imei' => '54321', 'status' => 'sold']);
            $this->fail('Sold device identity should be immutable.');
        } catch (ValidationException) {
            $this->assertSame('12345', $device->fresh()->imei);
        }
        $this->expectException(ValidationException::class);
        app(TrackedDeviceService::class)->delete($device);
    }

    public function test_arabic_devices_page_is_permission_protected_and_renders(): void
    {
        $permission = Permission::findOrCreate('page.devices.view', 'web');
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo($permission);
        $this->actingAs($user)->get('/devices')->assertOk()->assertSee('IMEI');
    }

    public function test_product_deletion_is_rejected_cleanly_when_device_history_exists(): void
    {
        $manageProducts = Permission::findOrCreate('page.products.manage', 'web');
        $manageDevices = Permission::findOrCreate('page.devices.manage', 'web');
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo([$manageProducts, $manageDevices]);
        $product = products::query()->create(['name' => 'Tracked phone', 'price' => '100.00', 'stock' => 0]);
        app(TrackedDeviceService::class)->create(['product_id' => $product->id, 'imei' => '555111222', 'status' => 'in_stock'], $user);
        $token = $user->createToken('products', ['api:me', 'page.products.manage']);

        $this->withToken($token->plainTextToken)->withHeader('Idempotency-Key', (string) Str::uuid())
            ->deleteJson('/api/v1/products/'.$product->id)->assertUnprocessable();
        $this->assertDatabaseHas('products', ['id' => $product->id]);

        $this->actingAs($user)->delete('/products/'.$product->id)->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_api_viewer_cannot_write_tracked_devices(): void
    {
        $viewPermission = Permission::findOrCreate('page.devices.view', 'web');
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo($viewPermission);
        $token = $user->createToken('devices-viewer', ['api:me', 'page.devices.view']);
        $product = products::query()->create(['name' => 'Phone', 'price' => '100.00', 'stock' => 1]);
        $this->withToken($token->plainTextToken)->withHeader('Idempotency-Key', (string) Str::uuid())
            ->postJson('/api/v1/devices', ['product_id' => $product->id, 'status' => 'in_stock'])->assertForbidden();
        $this->assertDatabaseCount('tracked_devices', 0);
    }

    public function test_api_device_writes_require_permission_and_idempotency_key(): void
    {
        $permission = Permission::findOrCreate('page.devices.manage', 'web');
        $viewPermission = Permission::findOrCreate('page.devices.view', 'web');
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo([$permission, $viewPermission]);
        $token = $user->createToken('devices', ['api:me', 'page.devices.manage', 'page.devices.view']);
        $product = products::query()->create(['name' => 'Phone', 'price' => '100.00', 'stock' => 1]);
        $payload = ['product_id' => $product->id, 'imei' => '998877665544', 'status' => 'in_stock'];
        $this->withToken($token->plainTextToken)->postJson('/api/v1/devices', $payload)->assertUnprocessable();
        $key = (string) Str::uuid();
        $created = $this->withToken($token->plainTextToken)->withHeader('Idempotency-Key', $key)->postJson('/api/v1/devices', $payload)->assertCreated();
        $id = $created->json('data.id');
        $this->withToken($token->plainTextToken)->withHeader('Idempotency-Key', $key)->postJson('/api/v1/devices', $payload)->assertCreated()->assertJsonPath('data.id', $id);
        $this->withToken($token->plainTextToken)->getJson('/api/v1/devices?q=998877')->assertOk()->assertJsonPath('data.0.id', $id);
    }
}
