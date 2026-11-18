<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('cost_price', 12, 2)->nullable()->after('price');
            $table->index('cost_price');
        });

        Schema::table('purchases', function (Blueprint $table): void {
            $table->foreignId('branch_id')->nullable()->after('supplier_id')->constrained('branches')->restrictOnDelete();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->string('payload_hash', 64)->nullable();
        });

        Schema::create('purchase_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('received_quantity')->default(0);
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
            $table->index(['purchase_id', 'product_id']);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_items') && DB::table('purchase_items')->exists()) {
            throw new RuntimeException('Rollback refused: received purchase item and stock receipt history exists.');
        }
        Schema::dropIfExists('purchase_items');
        Schema::table('purchases', function (Blueprint $table): void {
            $table->dropUnique(['idempotency_key']);
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['cost_price']);
            $table->dropColumn('cost_price');
        });
    }
};
