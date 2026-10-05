<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * هالِك: damaged / written-off stock.
 * Records are never deleted. A mistaken entry is voided, which returns the stock.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('damaged_items')) {
            return;
        }

        Schema::create('damaged_items', function (Blueprint $table): void {
            $table->id();
            // The product may be deleted later; the snapshot columns keep the history readable.
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name', 255);
            $table->string('product_barcode', 255)->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_value', 12, 2)->default(0);   // cost per unit (defaults to the selling price)
            $table->decimal('total_value', 14, 2)->default(0);  // quantity x unit_value
            $table->string('reason', 30)->index();
            $table->string('notes', 500)->nullable();
            $table->string('image_path', 255)->nullable();
            $table->date('damaged_on')->index();
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('status', 20)->default('recorded');  // recorded | voided
            $table->timestamp('voided_at')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable();
            $table->string('void_reason', 255)->nullable();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('created_by_name', 255)->nullable();
            $table->timestamps();

            $table->index(['status', 'damaged_on']);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('damaged_items') && DB::table('damaged_items')->exists()) {
            throw new \RuntimeException('Rollback refused: damaged_items contains records.');
        }

        Schema::dropIfExists('damaged_items');
    }
};
