<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\products;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SupplierAccountsPayableTest extends TestCase
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

    public function test_supplier_statement_shows_aging_buckets_and_account_mismatch_warning(): void
    {
        $supplier = Supplier::create([
            'name' => 'Statement supplier',
            'opening_balance' => '10.00',
            'balance' => '85.00',
        ]);
        $purchase = $supplier->purchases()->create([
            'invoice_number' => 'STATEMENT-40',
            'purchase_date' => now()->subDays(40)->toDateString(),
            'payment_type' => 'credit',
            'total_amount' => '75.00',
            'paid_amount' => '0.00',
            'remaining' => '75.00',
            'status' => 'unpaid',
        ]);
        $admin = User::where('system_account', 'admin')->firstOrFail();

        $this->actingAs($admin, 'web')->get(route('suppliers.statement', $supplier))
            ->assertOk()
            ->assertSee($purchase->invoice_number)
            ->assertSee('75.00 ج.م')
            ->assertDontSee('فرق غير موزع');
    }

    public function test_web_purchase_receipt_updates_branch_stock_and_preserves_manual_product_cost(): void
    {
        $supplier = Supplier::create(['name' => 'Receipt supplier', 'opening_balance' => '0.00', 'balance' => '0.00']);
        $product = products::query()->create([
            'name' => 'Receipt test phone', 'price' => '100.00', 'cost_price' => '60.00', 'description' => 'Test', 'stock' => 0,
        ]);
        $admin = User::where('system_account', 'admin')->firstOrFail();
        $branch = Branch::query()->where('is_main', true)->firstOrFail();
        $payload = [
            'idempotency_key' => (string) Str::uuid(),
            'branch_id' => $branch->id,
            'purchase_date' => now()->toDateString(),
            'payment_type' => 'credit',
            'paid_amount' => '5.00',
            'items' => [['product_id' => $product->id, 'quantity' => 2, 'unit_cost' => '12.50']],
        ];

        $this->actingAs($admin, 'web')->post(route('suppliers.receipts.store', $supplier), $payload)
            ->assertRedirect(route('suppliers.show', $supplier));
        $this->actingAs($admin, 'web')->post(route('suppliers.receipts.store', $supplier), $payload)
            ->assertRedirect(route('suppliers.show', $supplier));

        $this->assertSame('60.00', $product->fresh()->cost_price);
        $this->assertSame(2, (int) $product->fresh()->stock);
        $this->assertSame('20.00', $supplier->fresh()->balance);
        $this->assertDatabaseCount('purchases', 1);
        $this->assertDatabaseCount('purchase_items', 1);
        $this->assertDatabaseHas('branch_stock', ['branch_id' => $branch->id, 'product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_web_purchase_receipt_accepts_a_manually_named_item_without_stock_product(): void
    {
        $supplier = Supplier::create(['name' => 'Manual receipt supplier', 'opening_balance' => '0.00', 'balance' => '0.00']);
        $admin = User::where('system_account', 'admin')->firstOrFail();
        $branch = Branch::query()->where('is_main', true)->firstOrFail();
        $payload = [
            'idempotency_key' => (string) Str::uuid(),
            'branch_id' => $branch->id,
            'purchase_date' => now()->toDateString(),
            'payment_type' => 'credit',
            'paid_amount' => '0.00',
            'items' => [['product_id' => '', 'product_name' => 'قطعة غيار يدوية', 'quantity' => 3, 'unit_cost' => '12.50']],
        ];

        $this->actingAs($admin, 'web')->post(route('suppliers.receipts.store', $supplier), $payload)
            ->assertRedirect(route('suppliers.show', $supplier));

        $this->assertDatabaseHas('purchase_items', [
            'product_id' => null,
            'product_name' => 'قطعة غيار يدوية',
            'quantity' => 3,
            'line_total' => '37.50',
        ]);
        $this->assertSame('37.50', $supplier->fresh()->balance);
        $this->assertDatabaseMissing('branch_stock', ['branch_id' => $branch->id]);
    }

    public function test_supplier_payment_can_be_applied_to_one_purchase_invoice(): void
    {
        $supplier = Supplier::create(['name' => 'Targeted payment supplier', 'opening_balance' => '0.00', 'balance' => '0.00']);
        $admin = User::where('system_account', 'admin')->firstOrFail();
        $this->actingAs($admin, 'web')->post(route('suppliers.purchases.store', $supplier), [
            'purchase_date' => now()->toDateString(), 'payment_type' => 'credit', 'total_amount' => '80.00', 'paid_amount' => '0.00',
        ])->assertRedirect(route('suppliers.show', $supplier));
        $purchase = $supplier->purchases()->firstOrFail();

        $this->actingAs($admin, 'web')->post(route('suppliers.payments.store', $supplier), [
            'purchase_id' => $purchase->id, 'amount' => '30.00', 'payment_date' => now()->toDateString(),
        ])->assertRedirect(route('suppliers.show', $supplier));

        $this->assertSame('50.00', $purchase->fresh()->remaining);
        $this->assertSame('30.00', $purchase->fresh()->paid_amount);
        $this->assertSame('partial', $purchase->fresh()->status);
        $this->assertSame('50.00', $supplier->fresh()->balance);
        $this->assertDatabaseHas('supplier_payments', ['purchase_id' => $purchase->id, 'amount' => '30.00']);
    }

    public function test_credit_purchase_and_payment_keep_supplier_balance_and_invoice_status_in_sync(): void
    {
        $supplier = Supplier::create([
            'name' => 'مورد الاختبار',
            'opening_balance' => '0.00',
            'balance' => '0.00',
        ]);
        $admin = User::where('system_account', 'admin')->firstOrFail();

        $this->actingAs($admin, 'web')->post(route('suppliers.purchases.store', $supplier), [
            'purchase_date' => now()->toDateString(),
            'payment_type' => 'credit',
            'total_amount' => '100.00',
            'paid_amount' => '25.00',
        ])->assertRedirect(route('suppliers.show', $supplier));

        $supplier->refresh();
        $purchase = $supplier->purchases()->firstOrFail();
        $this->assertSame('75.00', $supplier->balance);
        $this->assertSame('75.00', $purchase->remaining);
        $this->assertSame('partial', $purchase->status);

        $this->actingAs($admin, 'web')->post(route('suppliers.payments.store', $supplier), [
            'amount' => '30.00',
            'payment_date' => now()->toDateString(),
        ])->assertRedirect(route('suppliers.show', $supplier));

        $supplier->refresh();
        $purchase->refresh();
        $this->assertSame('45.00', $supplier->balance);
        $this->assertSame('45.00', $purchase->remaining);
        $this->assertSame('55.00', $purchase->paid_amount);
        $this->assertSame(1, $supplier->payments()->count());

        $this->actingAs($admin, 'web')->get(route('suppliers.show', $supplier))
            ->assertOk()
            ->assertSee($purchase->invoice_number)
            ->assertSee('جزئية');

        $this->actingAs($admin, 'web')->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('الموردون والحسابات الدائنة')
            ->assertSee(route('suppliers.index'));
    }

    public function test_supplier_purchase_and_payment_histories_paginate_independently(): void
    {
        $supplier = Supplier::create([
            'name' => 'مورد الصفحات',
            'opening_balance' => '0.00',
            'balance' => '0.00',
        ]);

        foreach (range(1, 16) as $number) {
            $purchase = $supplier->purchases()->create([
                'invoice_number' => 'PUR-PAGE-'.$number,
                'purchase_date' => now()->subDays($number)->toDateString(),
                'payment_type' => 'credit',
                'total_amount' => '10.00',
                'paid_amount' => '0.00',
                'remaining' => '10.00',
                'status' => 'unpaid',
            ]);

            $supplier->payments()->create([
                'purchase_id' => $purchase->id,
                'receipt_number' => 'PAY-PAGE-'.$number,
                'amount' => '1.00',
                'payment_date' => now()->subDays($number)->toDateString(),
            ]);
        }

        $admin = User::where('system_account', 'admin')->firstOrFail();
        $this->actingAs($admin, 'web')->get(route('suppliers.show', $supplier))
            ->assertOk()
            ->assertSee('عرض 1 إلى 15 من 16 سجل')
            ->assertSee('purchases_page=2', false)
            ->assertSee('payments_page=2', false);

        $this->get(route('suppliers.show', ['supplier' => $supplier, 'purchases_page' => 2]))
            ->assertOk()
            ->assertSee('PUR-PAGE-16')
            ->assertSee('payments_page=2', false);
    }

    public function test_supplier_with_financial_history_cannot_be_deleted(): void
    {
        $supplier = Supplier::create([
            'name' => 'Supplier with history',
            'opening_balance' => '0.00',
            'balance' => '25.00',
        ]);
        $purchase = $supplier->purchases()->create([
            'invoice_number' => 'SUP-DELETE-GUARD',
            'purchase_date' => now()->toDateString(),
            'payment_type' => 'credit',
            'total_amount' => '25.00',
            'paid_amount' => '0.00',
            'remaining' => '25.00',
            'status' => 'unpaid',
        ]);
        $payment = $supplier->payments()->create([
            'purchase_id' => $purchase->id,
            'receipt_number' => 'SUP-DELETE-PAYMENT',
            'amount' => '5.00',
            'payment_date' => now()->toDateString(),
        ]);
        $admin = User::where('system_account', 'admin')->firstOrFail();

        $this->actingAs($admin, 'web')
            ->delete(route('suppliers.destroy', $supplier))
            ->assertSessionHasErrors('supplier');

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
        $this->assertDatabaseHas('purchases', ['id' => $purchase->id]);
        $this->assertDatabaseHas('supplier_payments', ['id' => $payment->id]);
    }
}
