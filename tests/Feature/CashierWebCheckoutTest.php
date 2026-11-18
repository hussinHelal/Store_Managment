<?php

namespace Tests\Feature;

use App\Models\products;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CashierWebCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_success_and_barcode_ui_filter_empty_nodes_and_request_live_camera_permission(): void
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
        $this->actingAs($user, 'web')->get(route('cashier.index'))->assertOk()
            ->assertSee('.filter(Boolean)', false)
            ->assertSee('cameraManualBarcode', false)
            ->assertSee('cameraManualForm', false);

        $scanner = file_get_contents(resource_path('js/app.js'));
        self::assertStringContainsString("typeof mediaDevices.getUserMedia !== 'function'", $scanner);
        self::assertStringContainsString('cameraRequest = mediaDevices.getUserMedia({', $scanner);
        self::assertStringContainsString('stream = await cameraRequest', $scanner);
        self::assertStringContainsString('reader.decodeFromStream(stream, video, onDecode)', $scanner);
        self::assertLessThan(strpos($scanner, 'import(\'@zxing/browser\')'), strpos($scanner, 'cameraRequest = mediaDevices.getUserMedia({'));
        self::assertStringNotContainsString('enumerateDevices', $scanner);
        self::assertStringNotContainsString('decodeFromConstraints', $scanner);
        self::assertStringNotContainsString('decodeFromImageUrl', $scanner);
    }

    public function test_cashier_success_response_contains_only_named_checkout_fields(): void
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
        $product = products::query()->create([
            'name' => 'POS product', 'price' => '12.50', 'description' => 'Checkout test', 'stock' => 3,
        ]);

        $this->actingAs($user, 'web')->postJson(route('cashier.checkout'), [
            'token' => (string) Str::uuid(),
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertOk()
            ->assertJsonStructure(['ok', 'invoice_id', 'invoice_number', 'total', 'paid', 'remaining', 'change', 'print_url'])
            ->assertJsonMissingPath('work')
            ->assertJsonPath('total', '12.50')
            ->assertJsonPath('remaining', '0.00');
    }
}
