<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('used_phones', function (Blueprint $table): void {
            $table->id();
            $table->string('brand', 100);
            $table->string('model', 160);
            $table->string('imei', 32)->nullable()->unique();
            $table->string('serial_number', 100)->nullable()->unique();
            $table->string('condition', 20)->index();
            $table->decimal('purchase_price', 12, 2);
            $table->decimal('asking_price', 12, 2);
            $table->date('purchase_date')->index();
            $table->string('seller_name', 160);
            $table->string('seller_phone', 32)->nullable();
            $table->string('status', 20)->default('in_stock')->index();
            $table->string('image_path', 255)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('used_phones');
    }
};
