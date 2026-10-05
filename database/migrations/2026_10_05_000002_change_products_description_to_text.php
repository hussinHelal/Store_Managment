<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * products.description was created as VARCHAR(255) but the product form accepts
 * up to 2000 characters, so a longer description crashed with a database error.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'description')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->text('description')->change();
            });
        }
    }

    public function down(): void
    {
        // The base products migration now defines description as text. Keep it canonical on rollback.
    }
};
