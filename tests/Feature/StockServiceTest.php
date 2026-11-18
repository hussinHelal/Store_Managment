<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\products;
use App\Models\StockMovement;
use App\Services\CurrentBranch;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_changes_are_locked_recorded_and_keep_the_aggregate_in_sync(): void
    {
        $product = $this->product(8);
        $branchId = app(CurrentBranch::class)->id();

        app(StockService::class)->decrease($product->id, 3, 'sale', note: 'اختبار البيع');

        $this->assertSame(5, (int) $product->fresh()->stock);
        $this->assertSame(5, (int) \DB::table('branch_stock')->where('branch_id', $branchId)->where('product_id', $product->id)->value('quantity'));
        $this->assertSame(['opening', 'sale'], StockMovement::query()->where('product_id', $product->id)->orderBy('id')->pluck('type')->all());
        $this->assertSame(0, app(StockService::class)->verify()['branch_stock_mismatches']);
        $this->assertSame(0, app(StockService::class)->verify()['movement_mismatches']);
    }

    public function test_stock_service_rejects_negative_branch_inventory_without_partial_writes(): void
    {
        $product = $this->product(1);

        try {
            app(StockService::class)->decrease($product->id, 2, 'sale');
            $this->fail('Expected insufficient stock validation.');
        } catch (ValidationException) {
            $this->assertSame(1, (int) $product->fresh()->stock);
            $this->assertDatabaseCount('stock_movements', 0);
            $this->assertDatabaseCount('branch_stock', 0);
        }
    }

    public function test_stock_movements_cannot_be_updated_or_deleted(): void
    {
        $product = $this->product(2);
        app(StockService::class)->decrease($product->id, 1, 'sale');
        $movement = StockMovement::query()->where('type', 'sale')->firstOrFail();

        try {
            $movement->update(['note' => 'rewrite']);
            $this->fail('Expected append-only update guard.');
        } catch (\LogicException $exception) {
            $this->assertSame('Stock movements are append-only.', $exception->getMessage());
        }

        $this->assertDatabaseHas('stock_movements', ['id' => $movement->id, 'note' => null]);
    }

    public function test_aggregate_stock_reduction_is_distributed_across_branches_with_movements(): void
    {
        $product = $this->product(8);
        $main = Branch::query()->where('is_main', true)->firstOrFail();
        app(StockService::class)->initializeBranch($product->id, $main->id);
        $other = Branch::query()->create(['name' => 'Secondary', 'code' => 'SECONDARY', 'is_active' => true]);
        \DB::table('branch_stock')->insert([
            'branch_id' => $other->id, 'product_id' => $product->id, 'quantity' => 0,
            'reorder_level' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);
        app(StockService::class)->increase($product->id, 5, 'purchase', branchId: $other->id);

        app(StockService::class)->setAggregate($product->id, 3, null, 'الإجمالي المستهدف');

        $this->assertSame(3, (int) $product->fresh()->stock);
        $this->assertSame(0, (int) \DB::table('branch_stock')->where('branch_id', $main->id)->where('product_id', $product->id)->value('quantity'));
        $this->assertSame(3, (int) \DB::table('branch_stock')->where('branch_id', $other->id)->where('product_id', $product->id)->value('quantity'));
        $this->assertSame(0, app(StockService::class)->verify()['branch_stock_mismatches']);
        $this->assertSame(0, app(StockService::class)->verify()['movement_mismatches']);
    }

    private function product(int $stock): products
    {
        return products::query()->create([
            'name' => 'Stock test '.$stock,
            'price' => '10.00',
            'description' => 'Feature test',
            'stock' => $stock,
        ]);
    }
}
