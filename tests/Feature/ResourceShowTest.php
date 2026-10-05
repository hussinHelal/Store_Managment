<?php

namespace Tests\Feature;

use App\Models\Maintenance;
use App\Models\User;
use App\Models\category;
use App\Models\customers;
use App\Models\invoice;
use App\Models\installments;
use App\Models\products;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResourceShowTest extends TestCase
{
    use RefreshDatabase;

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
    }

    public function test_existing_resource_show_routes_render_details(): void
    {
        $category = category::create(['name' => 'Test category']);
        $customer = customers::create(['name' => 'Test customer', 'address' => 'Test address', 'phone' => '001234']);
        $product = products::create([
            'name' => 'Test product',
            'price' => '5.25',
            'description' => 'Test description',
            'stock' => 5,
            'category_id' => $category->id,
        ]);
        $maintenance = Maintenance::create([
            'name' => 'Test device',
            'owner' => 'Test owner',
            'phone' => '001234',
            'address' => 'Test address',
            'description' => 'Test repair',
            'status' => 'قيد الانتظار',
            'requested_date' => now()->toDateString(),
        ]);
        $invoice = invoice::create([
            'invoice_number' => 'INV-DETAIL',
            'customer' => 'Test customer',
            'product_id' => $product->id,
            'quantity' => 1,
            'invoice_date' => now()->toDateString(),
            'total_amount' => '5.25',
            'product_price' => '5.25',
            'paid_amount' => '5.25',
            'status' => 'paid',
            'items' => [['product_id' => $product->id, 'name' => $product->name, 'quantity' => 1, 'price' => '5.25', 'line_total' => '5.25']],
        ]);
        $saleId = DB::table('sales')->insertGetId([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'total' => 525,
            'quantity' => 1,
            'payment_type' => 'cash',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $staff = User::factory()->create(['username' => 'show.staff']);
        $admin = User::where('system_account', 'admin')->firstOrFail();

        $this->actingAs($admin, 'web')->get(route('categories.show', $category))->assertOk();
        $this->actingAs($admin, 'web')->get(route('customers.show', $customer))->assertOk();
        $this->actingAs($admin, 'web')->get(route('products.show', $product))->assertOk();
        $this->actingAs($admin, 'web')->get(route('sales.show', $saleId))->assertOk();
        $this->actingAs($admin, 'web')->get(route('invoices.show', $invoice))->assertOk();
        $this->actingAs($admin, 'web')->get(route('maintenance.show', $maintenance))->assertOk();
        $this->actingAs($admin, 'web')->get(route('users.show', $staff))->assertOk();
    }

    public function test_customer_listing_shows_installment_due_and_payment_status(): void
    {
        $customer = customers::create([
            'name' => 'عميل أقساط',
            'address' => 'القاهرة',
            'phone' => '01012345678',
        ]);
        installments::create([
            'customer_id' => $customer->id,
            'customer' => $customer->name,
            'product_name' => 'منتج بالتقسيط',
            'product_price' => '50.00',
            'paid_amount' => '10.00',
            'remaining' => '40.00',
            'quantity' => 1,
            'status' => 'pending',
        ]);
        $admin = User::where('system_account', 'admin')->firstOrFail();

        installments::create([
            'customer_id' => $customer->id,
            'customer' => $customer->name,
            'product_name' => 'منتج مسدد',
            'product_price' => '50.00',
            'paid_amount' => '50.00',
            'remaining' => '0.00',
            'quantity' => 1,
            'status' => 'paid',
        ]);
        $response = $this->actingAs($admin, 'web')->get(route('customers.index'))
            ->assertOk()
            ->assertSee('40.00 ج.م')
            ->assertSee('إجمالي المستحقات من الأقساط')
            ->assertSee('سداد جزئي');

        $this->assertSame(1, substr_count($response->getContent(), '<span class="badge text-bg-warning">'));
    }

    public function test_product_listing_renders_images_from_public_storage(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/thumbnails/catalog-test.png', 'image');
        $product = products::create([
            'name' => 'منتج بصورة',
            'price' => '5.25',
            'description' => 'اختبار الصورة',
            'image' => 'products/catalog-test.png',
            'stock' => 5,
        ]);
        $admin = User::where('system_account', 'admin')->firstOrFail();

        $this->actingAs($admin, 'web')->get(route('products.index'))
            ->assertOk()
            ->assertSee(asset('storage/products/thumbnails/catalog-test.png'), false);
    }

    public function test_product_create_and_edit_pages_use_shared_barcode_scanner(): void
    {
        $category = category::create(['name' => 'تصنيف الباركود']);
        $product = products::create([
            'name' => 'منتج باركود',
            'price' => '5.25',
            'description' => 'اختبار قارئ الباركود',
            'stock' => 5,
            'category_id' => $category->id,
        ]);
        $admin = User::where('system_account', 'admin')->firstOrFail();

        $this->actingAs($admin, 'web')->get(route('products.create'))
            ->assertOk()
            ->assertSee('window.createBarcodeScanner');
        $this->actingAs($admin, 'web')->get(route('products.edit', $product))
            ->assertOk()
            ->assertSee('window.createBarcodeScanner');
    }

    public function test_maintenance_state_can_be_changed_to_refused_in_arabic(): void
    {
        $maintenance = Maintenance::create([
            'name' => 'جهاز مرفوض',
            'owner' => 'عميل الاختبار',
            'phone' => '01012345678',
            'address' => 'القاهرة',
            'description' => 'طلب صيانة',
            'status' => 'قيد الانتظار',
            'requested_date' => now()->toDateString(),
        ]);
        $admin = User::where('system_account', 'admin')->firstOrFail();

        $this->actingAs($admin, 'web')->put(route('maintenance.repaired', $maintenance), [
            'status' => 'مرفوض',
        ])->assertRedirect(route('maintenance.index'));

        $this->assertSame('مرفوض', $maintenance->fresh()->status);
        $this->assertNull($maintenance->fresh()->completed_date);
    }
}