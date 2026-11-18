<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\products;
use App\Models\User;
use App\Services\CurrentBranch;
use App\Services\StockService;
use App\Services\StocktakeService;
use App\Services\StockTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTransferAndStocktakeTest extends TestCase
{
    use RefreshDatabase;

    public function test_sent_transfer_decreases_available_stock_then_receive_reallocates_it(): void
    {
        $product = $this->product(7);
        $user = User::factory()->create(['is_active' => true]);
        $main = app(CurrentBranch::class)->id();
        $destination = Branch::query()->create(['name' => 'فرع الاختبار', 'code' => 'TEST', 'is_active' => true]);
        $service = app(StockTransferService::class);
        $transfer = $service->create([
            'from_branch_id' => $main,
            'to_branch_id' => $destination->id,
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
        ], $user, 'transfer-key-00000001');

        $service->send($transfer, $user);
        $this->assertSame('sent', $transfer->fresh()->status);
        $this->assertSame(4, (int) $product->fresh()->stock);

        $service->receive($transfer, $user);
        $this->assertSame('received', $transfer->fresh()->status);
        $this->assertSame(7, (int) $product->fresh()->stock);
        $this->assertSame(4, (int) \DB::table('branch_stock')->where('branch_id', $main)->where('product_id', $product->id)->value('quantity'));
        $this->assertSame(3, (int) \DB::table('branch_stock')->where('branch_id', $destination->id)->where('product_id', $product->id)->value('quantity'));
    }

    public function test_cancelling_sent_transfer_creates_a_compensating_source_movement(): void
    {
        $product = $this->product(5);
        $user = User::factory()->create(['is_active' => true]);
        $main = app(CurrentBranch::class)->id();
        $destination = Branch::query()->create(['name' => 'فرع آخر', 'code' => 'ALT', 'is_active' => true]);
        $service = app(StockTransferService::class);
        $transfer = $service->create([
            'from_branch_id' => $main,
            'to_branch_id' => $destination->id,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ], $user, 'transfer-key-00000002');
        $service->send($transfer, $user);
        $service->cancel($transfer, 'طلب مكرر', $user);

        $this->assertSame('cancelled', $transfer->fresh()->status);
        $this->assertSame(5, (int) $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'reference_type' => $transfer->getMorphClass(),
            'reference_id' => $transfer->id,
            'type' => 'transfer_cancel',
            'quantity_delta' => 2,
        ]);
    }

    public function test_stocktake_approval_preserves_sales_made_after_the_count(): void
    {
        $product = $this->product(10);
        $user = User::factory()->create(['is_active' => true]);
        $branchId = app(CurrentBranch::class)->id($user);
        $stocktakes = app(StocktakeService::class);
        $take = $stocktakes->start([
            'branch_id' => $branchId,
            'scope' => 'all',
        ], $user, 'stocktake-key-000001');
        $item = $take->items->firstOrFail();

        $stocktakes->count($take, $item->id, 9);
        app(StockService::class)->decrease($product->id, 2, 'sale', $user, 'بيع بعد العد');
        $stocktakes->review($take);
        $stocktakes->approve($take, $user);

        $this->assertSame('approved', $take->fresh()->status);
        $this->assertSame(-1, (int) $item->fresh()->difference);
        $this->assertSame(7, (int) $product->fresh()->stock);
        $this->assertSame(0, app(StockService::class)->verify()['branch_stock_mismatches']);
        $this->assertSame(0, app(StockService::class)->verify()['movement_mismatches']);
    }

    private function product(int $stock): products
    {
        return products::query()->create([
            'name' => 'Transfer test '.$stock,
            'price' => '10.00',
            'description' => 'Feature test',
            'stock' => $stock,
        ]);
    }
}
