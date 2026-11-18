<?php

use App\Support\Money;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->foreignId('cashier_id')->nullable()->after('product_id')->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->after('cashier_id')->constrained('branches')->nullOnDelete();
            $table->index(['invoice_date', 'status']);
            $table->index(['branch_id', 'invoice_date']);
            $table->index(['cashier_id', 'invoice_date']);
        });

        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->boolean('cost_estimated')->default(false);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
            $table->index(['invoice_id', 'product_id']);
            $table->index(['product_id', 'invoice_id']);
        });

        $mainBranchId = DB::table('branches')->where('is_main', true)->value('id');
        if ($mainBranchId !== null) {
            DB::table('invoices')->whereNull('branch_id')->update(['branch_id' => $mainBranchId]);
        }

        DB::table('invoices')->orderBy('id')->chunkById(250, function ($invoices): void {
            $productIds = $invoices->flatMap(function ($invoice): array {
                $items = json_decode((string) $invoice->items, true);

                return is_array($items)
                    ? collect($items)->pluck('product_id')->filter()->map(fn ($id) => (int) $id)->all()
                    : [(int) $invoice->product_id];
            })->unique()->values();
            $costs = DB::table('products')->whereIn('id', $productIds)->pluck('cost_price', 'id');

            foreach ($invoices as $invoice) {
                $items = json_decode((string) $invoice->items, true);
                if (! is_array($items) || $items === []) {
                    $items = [[
                        'product_id' => $invoice->product_id,
                        'name' => DB::table('products')->where('id', $invoice->product_id)->value('name') ?? 'منتج محذوف',
                        'price' => $invoice->product_price ?? $invoice->total_amount,
                        'quantity' => $invoice->quantity,
                        'line_total' => $invoice->total_amount,
                    ]];
                }

                foreach ($items as $item) {
                    $productId = isset($item['product_id']) ? (int) $item['product_id'] : null;
                    $price = (string) ($item['price'] ?? $invoice->product_price ?? '0.00');
                    $quantity = max(1, (int) ($item['quantity'] ?? 1));
                    $cost = $productId !== null ? $costs->get($productId) : null;
                    DB::table('invoice_items')->insert([
                        'invoice_id' => $invoice->id,
                        'product_id' => $productId,
                        'product_name' => (string) ($item['name'] ?? 'منتج'),
                        'quantity' => $quantity,
                        'unit_price' => $price,
                        'unit_cost' => $cost,
                        'cost_estimated' => true,
                        'line_total' => (string) ($item['line_total'] ?? Money::decimal(Money::cents($price) * $quantity)),
                        'created_at' => $invoice->created_at,
                        'updated_at' => $invoice->updated_at,
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropIndex(['invoice_date', 'status']);
            $table->dropIndex(['branch_id', 'invoice_date']);
            $table->dropIndex(['cashier_id', 'invoice_date']);
            $table->dropConstrainedForeignId('branch_id');
            $table->dropConstrainedForeignId('cashier_id');
        });
    }
};
