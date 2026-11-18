<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\products;
use App\Models\User;
use App\Services\StockService;
use App\Support\ApiPermissionMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiV1BranchStockTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
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
        $this->token = $this->admin->createToken('stock-test', app(ApiPermissionMap::class)->tokenAbilities($this->admin))->plainTextToken;
    }

    public function test_api_can_create_branch_filter_inventory_and_adjust_with_idempotency(): void
    {
        $product = products::query()->create(['name' => 'API stock phone', 'price' => '10.00', 'description' => 'Test', 'stock' => 0]);
        $branch = $this->withToken($this->token)->postJson('/api/v1/branches', [
            'name' => 'API branch', 'code' => 'API-1', 'is_active' => true,
        ])->assertCreated()->json('data');
        $this->assertDatabaseHas('branch_stock', ['branch_id' => $branch['id'], 'product_id' => $product->id, 'quantity' => 0]);

        $key = (string) Str::uuid();
        $payload = ['branch_id' => $branch['id'], 'product_id' => $product->id, 'quantity_delta' => 6, 'reason' => 'توريد موثق'];
        $this->withToken($this->token)->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/stock-movements/adjust', $payload)->assertOk()->assertJsonPath('data.quantity_delta', 6);
        $this->withToken($this->token)->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/stock-movements/adjust', $payload)->assertOk();
        $this->assertSame(6, (int) $product->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 1);
        $this->withToken($this->token)->getJson('/api/v1/branch-stock?branch_id='.$branch['id'])
            ->assertOk()->assertJsonPath('data.0.quantity', 6);
    }

    public function test_api_transfer_and_stocktake_flows_preserve_inventory_when_a_sale_follows_count(): void
    {
        $product = products::query()->create(['name' => 'Workflow phone', 'price' => '10.00', 'description' => 'Test', 'stock' => 10]);
        $main = Branch::query()->where('is_main', true)->firstOrFail();
        app(StockService::class)->initializeBranch($product->id, $main->id);

        $destination = Branch::query()->create(['name' => 'Target', 'code' => 'TARGET', 'is_active' => true]);
        \DB::table('branch_stock')->insert([
            'branch_id' => $destination->id, 'product_id' => $product->id, 'quantity' => 0,
            'reorder_level' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $transferKey = (string) Str::uuid();
        $transfer = $this->withToken($this->token)->withHeader('Idempotency-Key', $transferKey)
            ->postJson('/api/v1/stock-transfers', [
                'from_branch_id' => $main->id, 'to_branch_id' => $destination->id,
                'items' => [['product_id' => $product->id, 'quantity' => 2]],
            ])->assertCreated()->json('data');
        $this->withToken($this->token)->withHeader('Idempotency-Key', (string) Str::uuid())
            ->postJson('/api/v1/stock-transfers/'.$transfer['id'].'/send')->assertOk();
        $this->withToken($this->token)->withHeader('Idempotency-Key', (string) Str::uuid())
            ->postJson('/api/v1/stock-transfers/'.$transfer['id'].'/receive')->assertOk();

        $take = $this->withToken($this->token)->withHeader('Idempotency-Key', (string) Str::uuid())
            ->postJson('/api/v1/stocktakes', ['branch_id' => $main->id, 'scope' => 'all'])
            ->assertCreated()->json('data');
        $item = collect($take['items'])->firstWhere('product_id', $product->id);
        $this->withToken($this->token)->withHeader('Idempotency-Key', (string) Str::uuid())
            ->putJson('/api/v1/stocktakes/'.$take['id'].'/items/'.$item['id'], ['counted_qty' => 7])
            ->assertOk();
        app(StockService::class)->decrease($product->id, 1, 'sale', $this->admin, 'After count', branchId: $main->id);
        $this->withToken($this->token)->withHeader('Idempotency-Key', (string) Str::uuid())
            ->postJson('/api/v1/stocktakes/'.$take['id'].'/review')->assertOk();
        $this->withToken($this->token)->withHeader('Idempotency-Key', (string) Str::uuid())
            ->postJson('/api/v1/stocktakes/'.$take['id'].'/approve')->assertOk();

        $this->assertSame(8, (int) $product->fresh()->stock);
        $this->assertSame(6, (int) \DB::table('branch_stock')->where('branch_id', $main->id)->where('product_id', $product->id)->value('quantity'));
        $this->assertSame(0, app(StockService::class)->verify()['branch_stock_mismatches']);
        $this->assertSame(0, app(StockService::class)->verify()['movement_mismatches']);
    }

    public function test_cashier_lookup_reports_stock_for_the_users_branch_not_the_store_aggregate(): void
    {
        $product = products::query()->create(['name' => 'Remote stock phone', 'price' => '10.00', 'barcode' => 'REMOTE-1', 'description' => 'Test', 'stock' => 0]);
        $branch = Branch::query()->create(['name' => 'Other branch', 'code' => 'OTHER', 'is_active' => true]);
        \DB::table('branch_stock')->insert([
            'branch_id' => $branch->id, 'product_id' => $product->id, 'quantity' => 0,
            'reorder_level' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);
        app(StockService::class)->increase($product->id, 4, 'manual_adjust', $this->admin, 'Test other branch', branchId: $branch->id);

        $this->withToken($this->token)->getJson('/api/v1/cashier/products?q=REMOTE-1')
            ->assertOk()->assertJsonPath('data.0.stock', 0);
    }
}
