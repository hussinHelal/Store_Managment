<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
