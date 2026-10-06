<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\installments;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallmentPaymentTest extends TestCase
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

    public function test_payment_keeps_decimal_balances_and_does_not_write_missing_columns(): void
    {
        $installment = $this->makeInstallment();

        $this->actingAs($this->admin, 'web')->from(route('installments.index'))
            ->put(route('installments.pay', $installment), ['paid_amount' => '3.25'])
            ->assertRedirect(route('installments.index'));

        $installment->refresh();
        $this->assertSame('5.50', $installment->paid_amount);
        $this->assertSame('5.00', $installment->remaining);
        $this->assertSame('غير مكتمل', $installment->status);
    }

    public function test_payment_cannot_exceed_remaining_balance(): void
    {
        $installment = $this->makeInstallment();

        $this->actingAs($this->admin, 'web')->from(route('installments.index'))
            ->put(route('installments.pay', $installment), ['paid_amount' => '8.26'])
            ->assertRedirect(route('installments.index'))
            ->assertSessionHasErrors('paid_amount');

        $this->assertSame('2.25', $installment->fresh()->paid_amount);
        $this->assertSame('8.25', $installment->fresh()->remaining);
    }

    public function test_completed_installments_cannot_be_paid_again(): void
    {
        $installment = $this->makeInstallment();
        $installment->update(['remaining' => '0.00', 'status' => 'مكتمل']);

        $this->actingAs($this->admin, 'web')->get(route('installments.showPay', $installment))
            ->assertRedirect(route('installments.index'))
            ->assertSessionHas('error', 'هذا القسط مكتمل ولا يحتاج إلى دفعة أخرى.');

        $this->actingAs($this->admin, 'web')->from(route('installments.index'))
            ->put(route('installments.pay', $installment), ['paid_amount' => '0.01'])
            ->assertRedirect(route('installments.index'))
            ->assertSessionHasErrors('paid_amount');

        $this->assertSame('0.00', $installment->fresh()->remaining);
    }

    public function test_product_search_requires_two_characters_and_returns_matching_products(): void
    {
        $productId = DB::table('products')->insertGetId([
            'name' => 'Searchable phone',
            'price' => '5.25',
            'description' => 'Test item',
            'stock' => 10,
            'total_sold' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->admin, 'web')
            ->getJson(route('installments.products.search', ['q' => 'S']))
            ->assertOk()
            ->assertExactJson(['data' => []]);

        $this->getJson(route('installments.products.search', ['q' => 'Se']))
            ->assertOk()
            ->assertJsonPath('data.0.id', $productId)
            ->assertJsonPath('data.0.name', 'Searchable phone');
    }

    public function test_installment_create_page_renders_the_product_field_template(): void
    {
        $this->actingAs($this->admin, 'web')
            ->get(route('installments.create'))
            ->assertOk()
            ->assertSee('id="installment-items-container"', false)
            ->assertSee('class="form-control product-search"', false);
    }

    public function test_create_and_update_reject_overpayments_and_mismatched_item_arrays(): void
    {
        $productId = DB::table('products')->insertGetId([
            'name' => 'Installment product',
            'price' => '5.25',
            'description' => 'Test item',
            'stock' => 10,
            'total_sold' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->admin, 'web')->from(route('installments.create'))
            ->post(route('installments.store'), [
                'customer' => 'Test customer',
                'product_ids' => [$productId],
                'quantities' => [1, 2],
                'paid_amount' => '1.00',
            ])->assertRedirect(route('installments.create'))->assertSessionHasErrors('quantities');

        $this->actingAs($this->admin, 'web')->from(route('installments.create'))
            ->post(route('installments.store'), [
                'customer' => 'Test customer',
                'product_ids' => [$productId],
                'quantities' => [1],
                'paid_amount' => '5.26',
            ])->assertRedirect(route('installments.create'))->assertSessionHasErrors('paid_amount');

        $this->actingAs($this->admin, 'web')->post(route('installments.store'), [
            'customer' => 'Test customer',
            'product_ids' => [$productId],
            'quantities' => [1],
            'paid_amount' => '1.25',
            'notes' => 'اتصال بالعميل',
        ])->assertRedirect(route('installments.index'));

        $installment = installments::firstOrFail();
        $this->assertSame('اتصال بالعميل', $installment->notes);
        $this->actingAs($this->admin, 'web')->from(route('installments.index'))
            ->put(route('installments.update', $installment), [
                'customer' => 'Test customer',
                'product_ids' => [$productId],
                'quantities' => [1],
                'paid_amount' => '5.26',
            ])->assertRedirect(route('installments.index'))->assertSessionHasErrors('paid_amount');

        $this->assertSame('1.25', $installment->fresh()->paid_amount);
        $this->assertSame('4.00', $installment->fresh()->remaining);
    }

    private function makeInstallment(): installments
    {
        return installments::create([
            'customer' => 'Test customer',
            'product_name' => 'Test product',
            'product_price' => '10.50',
            'paid_amount' => '2.25',
            'remaining' => '8.25',
            'quantity' => 1,
            'status' => 'غير مكتمل',
        ]);
    }
}
