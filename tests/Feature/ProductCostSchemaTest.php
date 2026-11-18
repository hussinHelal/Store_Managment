<?php

namespace Tests\Feature;

use App\Models\products;
use App\Models\User;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductCostSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_list_cost_labels_are_arabic_and_use_egyptian_currency(): void
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
        products::query()->create(['name' => 'Cost test phone', 'price' => '120.00', 'cost_price' => '75.50', 'description' => 'Phone', 'stock' => 0]);
        $user = User::query()->where('system_account', 'superadmin')->firstOrFail();

        $this->actingAs($user)->get('/products')->assertOk()
            ->assertSee('تكلفة الوحدة')->assertSee('75.50 ج.م')->assertDontSee('????');
    }

    public function test_api_cost_edit_on_a_database_without_cost_column_returns_validation_instead_of_500(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo(Permission::findOrCreate('page.products.manage', 'web'));
        $token = $user->createToken('product-manager', ['api:me', 'page.products.manage'])->plainTextToken;
        $product = products::query()->create(['name' => 'Test phone', 'price' => '120.00', 'description' => 'Phone', 'stock' => 0]);
        Schema::shouldReceive('hasColumn')->once()->with('products', 'cost_price')->andReturn(false);

        $this->withToken($token)->withHeader('Idempotency-Key', (string) Str::uuid())
            ->putJson('/api/v1/products/'.$product->id, [
                'name' => 'Test phone', 'price' => '120.00', 'cost_price' => '75.50',
                'description' => 'Phone', 'stock' => 0,
            ])->assertUnprocessable()
            ->assertJsonValidationErrors('cost_price')
            ->assertJsonPath('errors.cost_price.0', 'يلزم تحديث قاعدة البيانات قبل حفظ تكلفة المنتج. لم يتم حفظ التغيير.');
        $this->assertSame(12000, Money::cents($product->fresh()->price));
    }

    public function test_product_edit_disables_cost_input_when_database_migration_is_pending(): void
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
        $product = products::query()->create(['name' => 'Schema test', 'price' => '120.00', 'description' => 'Phone', 'stock' => 0]);
        $user = User::query()->where('system_account', 'superadmin')->firstOrFail();
        Schema::shouldReceive('hasColumn')->once()->with('products', 'cost_price')->andReturn(false);

        $this->actingAs($user)->get('/products/'.$product->id.'/edit')->assertOk()
            ->assertSee('id="cost_price"', false)->assertSee('disabled', false)
            ->assertSee('تتطلب هذه الميزة تحديث قاعدة البيانات');
    }
}
