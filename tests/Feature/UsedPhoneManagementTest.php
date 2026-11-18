<?php

namespace Tests\Feature;

use App\Models\UsedPhone;
use App\Models\User;
use App\Services\UsedPhoneService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UsedPhoneManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_used_phone_crud_is_validated_permission_gated_and_idempotent(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo([
            Permission::findOrCreate('page.used_phones.view', 'web'),
            Permission::findOrCreate('page.used_phones.manage', 'web'),
        ]);
        $token = $user->createToken('used-phones', ['api:me', 'page.used_phones.view', 'page.used_phones.manage'])->plainTextToken;
        $payload = [
            'brand' => 'Samsung', 'model' => 'Galaxy S22', 'imei' => '356789012345678', 'serial_number' => 'S22-ABC',
            'condition' => 'good', 'purchase_price' => '10000.00', 'asking_price' => '12500.00',
            'purchase_date' => '2026-11-18', 'seller_name' => 'Previous owner', 'seller_phone' => '01012345678',
            'status' => 'in_stock', 'notes' => 'Screen inspected',
        ];

        $this->withToken($token)->postJson('/api/v1/used-phones', $payload)->assertUnprocessable();
        $key = (string) Str::uuid();
        $created = $this->withToken($token)->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/used-phones', $payload)->assertCreated()
            ->assertJsonPath('data.brand', 'Samsung')->assertJsonPath('data.asking_price', '12500.00');
        $id = $created->json('data.id');
        $this->withToken($token)->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/used-phones', $payload)->assertCreated()->assertJsonPath('data.id', $id);
        $this->withToken($token)->getJson('/api/v1/used-phones?search=Samsung')->assertOk()->assertJsonPath('data.0.id', $id);

        $this->withToken($token)->withHeader('Idempotency-Key', (string) Str::uuid())
            ->putJson('/api/v1/used-phones/'.$id, array_replace($payload, ['asking_price' => '13000.00']))
            ->assertOk()->assertJsonPath('data.asking_price', '13000.00');
        $this->withToken($token)->withHeader('Idempotency-Key', (string) Str::uuid())
            ->deleteJson('/api/v1/used-phones/'.$id)->assertOk();
        $this->assertDatabaseMissing('used_phones', ['id' => $id]);
    }

    public function test_arabic_used_phone_page_has_create_edit_fields_and_delete_control(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo([
            Permission::findOrCreate('page.used_phones.view', 'web'),
            Permission::findOrCreate('page.used_phones.manage', 'web'),
        ]);
        UsedPhone::query()->create([
            'brand' => 'Apple', 'model' => 'iPhone 13', 'condition' => 'excellent', 'purchase_price' => '8000.00',
            'asking_price' => '10000.00', 'purchase_date' => '2026-11-18', 'seller_name' => 'Seller', 'status' => 'in_stock',
            'image_path' => 'used-phones/test.jpg',
        ]);
        $this->actingAs($user)->get('/used-phones')->assertOk()
            ->assertSee('الموبايلات المستعملة')->assertSee('name="purchase_price"', false)
            ->assertSee('name="asking_price"', false)->assertSee('name="image"', false)
            ->assertSee('name="remove_image"', false)->assertSee('حذف');
    }

    public function test_sold_used_phone_cannot_be_deleted(): void
    {
        $phone = UsedPhone::query()->create([
            'brand' => 'Apple', 'model' => 'iPhone', 'condition' => 'good', 'purchase_price' => '100.00',
            'asking_price' => '150.00', 'purchase_date' => '2026-11-18', 'seller_name' => 'Seller', 'status' => 'sold',
        ]);

        $this->expectException(ValidationException::class);
        app(UsedPhoneService::class)->delete($phone);
    }

    public function test_web_manager_can_create_update_and_delete_used_phone(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo([
            Permission::findOrCreate('page.used_phones.view', 'web'),
            Permission::findOrCreate('page.used_phones.manage', 'web'),
        ]);
        $payload = [
            'brand' => 'Google', 'model' => 'Pixel 8', 'imei' => '356700000000001', 'serial_number' => 'PIXEL-8-1',
            'condition' => 'excellent', 'purchase_price' => '9000.00', 'asking_price' => '11000.00',
            'purchase_date' => '2026-11-18', 'seller_name' => 'Previous owner', 'seller_phone' => '',
            'status' => 'in_stock', 'notes' => 'Battery tested',
        ];

        $this->actingAs($user)->post('/used-phones', $payload)->assertRedirect(route('used_phones.index'));
        $phone = UsedPhone::query()->firstOrFail();
        $this->assertSame('9000.00', $phone->purchase_price);
        $this->actingAs($user)->put('/used-phones/'.$phone->id, array_replace($payload, ['asking_price' => '12000.00']))
            ->assertRedirect(route('used_phones.index'));
        $this->assertSame('12000.00', $phone->fresh()->asking_price);
        $this->actingAs($user)->delete('/used-phones/'.$phone->id)->assertRedirect(route('used_phones.index'));
        $this->assertDatabaseMissing('used_phones', ['id' => $phone->id]);
    }
}
