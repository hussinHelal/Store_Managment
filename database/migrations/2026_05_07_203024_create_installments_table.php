<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable()->after('id');
            $table->string('customer')->default('مجهول');
            $table->foreignId('product_id')->nullable()->constrained('products')->after('customer');
            $table->string('product_name');
            $table->integer('product_price');
            $table->text('items')->nullable();
            $table->date('payment_date')->nullable();
            $table->date('next_payment_date')->nullable();
            $table->integer('paid_amount')->default(0);
            $table->integer('remaining')->default(0);
            $table->integer('quantity')->default(1);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
