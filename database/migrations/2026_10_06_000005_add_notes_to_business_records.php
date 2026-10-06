<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = ['products', 'invoices', 'installments', 'maintenances'];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'notes')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->text('notes')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'notes')) {
                if (DB::table($tableName)->whereNotNull('notes')->where('notes', '<>', '')->exists()) {
                    throw new \RuntimeException('Rollback refused: saved notes in '.$tableName.' would be lost.');
                }

                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropColumn('notes');
                });
            }
        }
    }
};
