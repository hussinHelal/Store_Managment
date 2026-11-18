<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\products;
use App\Models\User;
use App\Services\CurrentBranch;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BranchStockPagesTest extends TestCase
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
        $this->admin = User::query()->where('system_account', 'admin')->firstOrFail();
    }

    public function test_branch_inventory_movements_transfers_and_stocktakes_have_arabic_pages(): void
    {
        $this->actingAs($this->admin, 'web')
            ->get(route('branch_stock.index'))->assertOk()->assertSee('مخزون الفروع')->assertSee('إضافة فرع')
            ->assertSee('تحويلات المخزون');
        $this->get(route('stock_movements.index'))->assertOk()->assertSee('حركات المخزون');
        $this->get(route('branch_stock.transfers.index'))->assertOk()->assertSee('تحويلات المخزون بين الفروع');
        $this->get(route('stocktake.index'))->assertOk()->assertSee('جرد المخزون')->assertSee('بدء جرد جديد');
    }

    public function test_branch_creation_seeds_zero_balances_and_manual_adjustment_is_logged(): void
    {
        $product = products::query()->create(['name' => 'New test product', 'price' => '20.00', 'description' => 'Test', 'stock' => 0]);
        $response = $this->actingAs($this->admin, 'web')->post(route('branch_stock.branches.store'), [
            'name' => 'فرع الهرم', 'code' => 'HARAM', 'address' => 'الجيزة',
        ]);
        $branch = Branch::query()->where('code', 'HARAM')->firstOrFail();
        $response->assertRedirect();
        $this->assertDatabaseHas('branch_stock', ['branch_id' => $branch->id, 'product_id' => $product->id, 'quantity' => 0]);

        $response = $this->post(route('branch_stock.adjust'), [
            'idempotency_key' => (string) Str::uuid(),
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity_delta' => 4,
            'reason' => 'رصيد افتتاحي موثق',
        ]);
        $response->assertRedirect();
        $this->assertSame(4, (int) $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'type' => 'manual_adjust',
            'quantity_delta' => 4,
            'note' => 'رصيد افتتاحي موثق',
        ]);
        $this->assertSame(0, app(StockService::class)->verify()['branch_stock_mismatches']);
    }

    public function test_main_branch_cannot_be_deactivated(): void
    {
        $mainId = app(CurrentBranch::class)->id();
        $this->actingAs($this->admin, 'web')->put(route('branch_stock.branches.update', $mainId), [
            'name' => 'الفرع الرئيسي', 'code' => 'MAIN', 'is_active' => 0,
        ])->assertSessionHasErrors('is_active');
    }

    public function test_stocktake_review_page_keeps_count_fields_open_for_corrections(): void
    {
        $product = products::query()->create(['name' => 'Reviewable stock item', 'price' => '20.00', 'description' => 'Test', 'stock' => 5]);
        $user = $this->admin;
        $take = app(\App\Services\StocktakeService::class)->start([
            'branch_id' => app(CurrentBranch::class)->id($user),
            'scope' => 'all',
        ], $user, (string) Str::uuid());
        $item = $take->items->firstOrFail();
        $stocktakes = app(\App\Services\StocktakeService::class);
        $stocktakes->count($take, $item->id, 4);
        $stocktakes->review($take);

        $response = $this->actingAs($user, 'web')->get(route('stocktake.show', $take));
        $response->assertOk()->assertSee('name="counted_qty"', false)->assertSee('name="note"', false)->assertSee('-1');
        $this->assertSame(-1, (int) $item->fresh()->difference);

        $this->put(route('stocktake.count', [$take, $item->id]), ['counted_qty' => 5, 'note' => 'إعادة العد'])
            ->assertRedirect();
        $this->assertSame(0, (int) $item->fresh()->difference);
    }

    public function test_branch_stock_success_flash_is_rendered_only_once(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->withSession(['success' => 'تم تحديث أرصدة الفرع'])
            ->get(route('branch_stock.index'));

        $response->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), 'تم تحديث أرصدة الفرع'));
    }
}
