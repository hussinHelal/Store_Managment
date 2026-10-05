<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * customers.phone was created as an INTEGER, which drops the leading zero
 * (01012345678 -> 1012345678) and overflows on longer numbers such as
 * +20 numbers. Same fix already applied to maintenances.phone.
 *
 * Existing values keep their current digits. Leading zeros that were already
 * lost cannot be recovered automatically.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'phone')) {
            Schema::table('customers', function (Blueprint $table): void {
                $table->string('phone', 32)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // The base customers migration now defines phone as a string. Keep it canonical on rollback.
    }
};
