<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('products')->where('stock', '<', 0)->exists()) {
            throw new RuntimeException('Branch stock migration stopped: products with negative stock must be reviewed before migration.');
        }

        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('code', 32)->unique();
            $table->string('address')->nullable();
            $table->string('phone', 32)->nullable();
            $table->boolean('is_main')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('branch_stock', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('reorder_level')->default(0);
            $table->timestamps();
            $table->unique(['branch_id', 'product_id']);
            $table->index(['branch_id', 'quantity']);
            $table->index(['product_id', 'quantity']);
        });

        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('type', 32)->index();
            $table->integer('quantity_delta');
            $table->unsignedInteger('balance_after');
            $table->nullableMorphs('reference');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note', 500)->nullable();
            $table->string('idempotency_key', 80)->nullable()->unique();
            $table->timestamps();
            $table->index(['branch_id', 'product_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });

        Schema::create('stock_transfers', function (Blueprint $table): void {
            $table->id();
            $table->string('number', 40)->unique();
            $table->foreignId('from_branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('to_branch_id')->constrained('branches')->restrictOnDelete();
            $table->string('status', 16)->default('draft')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->string('note', 500)->nullable();
            $table->string('idempotency_key', 80)->nullable()->unique();
            $table->timestamps();
            $table->index(['from_branch_id', 'created_at']);
            $table->index(['to_branch_id', 'created_at']);
        });

        Schema::create('stock_transfer_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
            $table->unique(['stock_transfer_id', 'product_id']);
            $table->index('product_id');
        });

        Schema::create('stocktakes', function (Blueprint $table): void {
            $table->id();
            $table->string('number', 40)->unique();
            $table->foreignId('branch_id')->constrained()->restrictOnDelete();
            $table->string('scope', 16)->default('all');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('status', 16)->default('draft')->index();
            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('snapshot_at')->nullable();
            $table->unsignedBigInteger('snapshot_movement_id')->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->string('note', 500)->nullable();
            $table->string('idempotency_key', 80)->nullable()->unique();
            $table->timestamps();
            $table->index(['branch_id', 'created_at']);
        });

        Schema::create('stocktake_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('stocktake_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedInteger('system_qty');
            $table->unsignedInteger('counted_qty')->nullable();
            $table->integer('difference')->nullable();
            $table->timestamp('counted_at')->nullable();
            $table->unsignedBigInteger('counted_movement_id')->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();
            $table->unique(['stocktake_id', 'product_id']);
            $table->index('product_id');
        });

        $mainBranchId = DB::table('branches')->insertGetId([
            'name' => 'الفرع الرئيسي',
            'code' => 'MAIN',
            'is_main' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (! Schema::hasColumn('users', 'branch_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            });
        }
        DB::table('users')->whereNull('branch_id')->update(['branch_id' => $mainBranchId]);

        DB::table('products')->orderBy('id')->chunk(500, function ($products) use ($mainBranchId): void {
            foreach ($products as $product) {
                $quantity = max(0, (int) $product->stock);
                DB::table('branch_stock')->insert([
                    'branch_id' => $mainBranchId,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'reorder_level' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($quantity > 0) {
                    DB::table('stock_movements')->insert([
                        'branch_id' => $mainBranchId,
                        'product_id' => $product->id,
                        'type' => 'opening',
                        'quantity_delta' => $quantity,
                        'balance_after' => $quantity,
                        'note' => 'رصيد افتتاحي عند تفعيل مخزون الفروع',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocktake_items');
        Schema::dropIfExists('stocktakes');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        if (Schema::hasColumn('users', 'branch_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('branch_id');
            });
        }
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('branch_stock');
        Schema::dropIfExists('branches');
    }
};
