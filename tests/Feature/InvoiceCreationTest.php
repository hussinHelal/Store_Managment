<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InvoiceCreationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected int $productId;

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
        $this->productId = DB::table('products')->insertGetId([
            'name' => 'Test phone',
            'price' => '2.50',
            'description' => 'Test item',
            'stock' => 4,
            'total_sold' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_invoice_aggregates_repeated_products_and_updates_stock_atomically(): void
    {
        $this->actingAs($this->admin, 'web')->post(route('invoices.store'), [
            'customer' => 'Test customer',
            'product_ids' => [$this->productId, $this->productId],
            'quantities' => [1, 2],
            'invoice_date' => now()->toDateString(),
            'paid_amount' => '2.50',
            'notes' => 'اتصال قبل التسليم',
        ])->assertRedirect(route('invoices.index'));

        $this->assertDatabaseHas('products', [
            'id' => $this->productId,
            'stock' => 1,
            'total_sold' => 3,
        ]);
        $this->assertDatabaseCount('invoices', 1);

        $invoice = DB::table('invoices')->first();
        $this->assertEquals(7.5, (float) $invoice->total_amount);
        $this->assertSame(3, $invoice->quantity);
        $this->assertSame('اتصال قبل التسليم', $invoice->notes);
        $this->assertNotSame('INV-'.now()->timestamp, $invoice->invoice_number);
    }

    public function test_insufficient_stock_and_overpayment_leave_inventory_and_invoices_unchanged(): void
    {
        $this->actingAs($this->admin, 'web')->from(route('invoices.create'))->post(route('invoices.store'), [
            'customer' => 'Test customer',
            'product_ids' => [$this->productId, $this->productId],
            'quantities' => [3, 2],
            'invoice_date' => now()->toDateString(),
            'paid_amount' => '1.00',
        ])->assertRedirect(route('invoices.create'))->assertSessionHasErrors('product_ids');

        $this->actingAs($this->admin, 'web')->from(route('invoices.create'))->post(route('invoices.store'), [
            'customer' => 'Test customer',
            'product_ids' => [$this->productId],
            'quantities' => [1],
            'invoice_date' => now()->toDateString(),
            'paid_amount' => '2.51',
        ])->assertRedirect(route('invoices.create'))->assertSessionHasErrors('paid_amount');

        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseHas('products', ['id' => $this->productId, 'stock' => 4, 'total_sold' => 0]);
    }

    public function test_multi_product_refund_restores_each_line_and_closes_the_linked_installment(): void
    {
        $secondProductId = $this->createProduct('Second phone', '3.50', 5);
        $this->actingAs($this->admin, 'web')->post(route('invoices.store'), [
            'customer' => 'Test customer',
            'product_ids' => [$this->productId, $secondProductId],
            'quantities' => [2, 1],
            'invoice_date' => now()->toDateString(),
            'paid_amount' => '0.00',
        ])->assertRedirect(route('invoices.index'));

        $invoice = \App\Models\invoice::firstOrFail();
        $this->actingAs($this->admin, 'web')->post(route('invoices.refund', $invoice))
            ->assertRedirect(route('invoices.index'));

        $this->assertDatabaseHas('products', ['id' => $this->productId, 'stock' => 4, 'total_sold' => 0]);
        $this->assertDatabaseHas('products', ['id' => $secondProductId, 'stock' => 5, 'total_sold' => 0]);
        $this->assertSame('refunded', $invoice->fresh()->status);
        $installment = \App\Models\installments::where('invoice_id', $invoice->id)->firstOrFail();
        $this->assertSame('مسترد', $installment->status);
        $this->assertSame('0.00', $installment->remaining);
    }

    public function test_invoice_update_reconciles_stock_and_linked_history_cannot_be_deleted(): void
    {
        $secondProductId = $this->createProduct('Second phone', '3.50', 5);
        $this->actingAs($this->admin, 'web')->post(route('invoices.store'), [
            'customer' => 'Test customer',
            'product_ids' => [$this->productId],
            'quantities' => [2],
            'invoice_date' => now()->toDateString(),
            'paid_amount' => '0.00',
        ])->assertRedirect(route('invoices.index'));

        $invoice = \App\Models\invoice::firstOrFail();
        $this->actingAs($this->admin, 'web')->put(route('invoices.update', $invoice), [
            'customer' => 'Test customer',
            'product_ids' => [$secondProductId],
            'quantities' => [1],
            'invoice_date' => now()->toDateString(),
            'paid_amount' => '0.00',
        ])->assertRedirect(route('invoices.index'));

        $this->assertDatabaseHas('products', ['id' => $this->productId, 'stock' => 4, 'total_sold' => 0]);
        $this->assertDatabaseHas('products', ['id' => $secondProductId, 'stock' => 4, 'total_sold' => 1]);

        $this->actingAs($this->admin, 'web')->delete(route('invoices.destroy', $invoice))
            ->assertRedirect(route('invoices.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    public function test_product_with_invoice_history_cannot_be_deleted(): void
    {
        $this->actingAs($this->admin, 'web')->post(route('invoices.store'), [
            'customer' => 'Test customer',
            'product_ids' => [$this->productId],
            'quantities' => [1],
            'invoice_date' => now()->toDateString(),
            'paid_amount' => '2.50',
        ])->assertRedirect(route('invoices.index'));

        $this->actingAs($this->admin, 'web')->delete(route('products.destroy', $this->productId))
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseHas('products', ['id' => $this->productId]);
    }

    private function createProduct(string $name, string $price, int $stock): int
    {
        return DB::table('products')->insertGetId([
            'name' => $name,
            'price' => $price,
            'description' => 'Test item',
            'stock' => $stock,
            'total_sold' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
