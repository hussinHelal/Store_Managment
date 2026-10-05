<?php

namespace Tests\Feature;

use App\Models\Maintenance;
use App\Models\Supplier;
use App\Models\User;
use App\Models\products;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessRecordNotesTest extends TestCase
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

    public function test_product_notes_persist_on_create_and_edit(): void
    {
        $this->actingAs($this->admin, 'web')->post(route('products.store'), [
            'name' => 'موبايل اختبار',
            'price' => '25.00',
            'description' => 'وصف المنتج',
            'notes' => 'ملاحظة أولى',
            'stock' => 5,
        ])->assertRedirect(route('products.index'));

        $product = products::where('name', 'موبايل اختبار')->firstOrFail();
        $this->assertSame('ملاحظة أولى', $product->notes);

        $this->actingAs($this->admin, 'web')->put(route('products.update', $product), [
            'name' => $product->name,
            'price' => '25.00',
            'description' => 'وصف المنتج',
            'notes' => 'ملاحظة محدثة',
            'stock' => 5,
        ])->assertRedirect(route('products.index'));

        $this->assertSame('ملاحظة محدثة', $product->fresh()->notes);
    }

    public function test_maintenance_notes_persist_on_create_and_edit(): void
    {
        $this->actingAs($this->admin, 'web')->post(route('maintenance.store'), [
            'name' => 'جهاز اختبار',
            'owner' => 'عميل اختبار',
            'description' => 'وصف العطل',
            'notes' => 'ملاحظة أولى',
            'status' => 'قيد الانتظار',
            'phone' => '01012345678',
            'address' => 'القاهرة',
            'requested_date' => now()->toDateString(),
        ])->assertRedirect(route('maintenance.index'));

        $maintenance = Maintenance::where('name', 'جهاز اختبار')->firstOrFail();
        $this->assertSame('ملاحظة أولى', $maintenance->notes);

        $this->actingAs($this->admin, 'web')->put(route('maintenance.update', $maintenance), [
            'name' => $maintenance->name,
            'owner' => $maintenance->owner,
            'description' => $maintenance->description,
            'notes' => 'ملاحظة محدثة',
            'status' => 'قيد الانتظار',
            'phone' => $maintenance->phone,
            'address' => $maintenance->address,
            'requested_date' => $maintenance->requested_date->toDateString(),
        ])->assertRedirect(route('maintenance.index'));

        $this->assertSame('ملاحظة محدثة', $maintenance->fresh()->notes);
    }

    public function test_supplier_notes_persist_on_create_and_edit(): void
    {
        $this->actingAs($this->admin, 'web')->post(route('suppliers.store'), [
            'name' => 'مورد اختبار',
            'opening_balance' => '0.00',
            'notes' => 'ملاحظة أولى',
        ])->assertRedirect(route('suppliers.index'));

        $supplier = Supplier::where('name', 'مورد اختبار')->firstOrFail();
        $this->assertSame('ملاحظة أولى', $supplier->notes);

        $this->actingAs($this->admin, 'web')->put(route('suppliers.update', $supplier), [
            'name' => $supplier->name,
            'opening_balance' => $supplier->opening_balance,
            'notes' => 'ملاحظة محدثة',
        ])->assertRedirect(route('suppliers.show', $supplier));

        $this->assertSame('ملاحظة محدثة', $supplier->fresh()->notes);
    }
}
