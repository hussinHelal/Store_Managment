<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Wallets may be saved with any field empty, and two wallets may share the same
 * name or the same number. Drops the (provider, identifier) unique index and makes
 * name, provider and identifier nullable. Safe to run on a database that already
 * has wallets, and safe to run twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wallets')) {
            return;
        }

        foreach (Schema::getIndexes('wallets') as $index) {
            $columns = $index['columns'] ?? [];

            if (($index['unique'] ?? false) && in_array('identifier', $columns, true)) {
                $name = $index['name'];
                Schema::table('wallets', fn (Blueprint $table) => $table->dropUnique($name));
            }
        }

        Schema::table('wallets', function (Blueprint $table): void {
            if (Schema::hasColumn('wallets', 'name')) {
                $table->string('name', 100)->nullable()->change();
            }
            if (Schema::hasColumn('wallets', 'provider')) {
                $table->string('provider', 30)->nullable()->change();
            }
            if (Schema::hasColumn('wallets', 'identifier')) {
                $table->string('identifier', 64)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('wallets')) {
            return;
        }

        $hasEmpty = DB::table('wallets')->where(fn ($q) => $q->whereNull('name')->orWhereNull('provider')->orWhereNull('identifier'))->exists();
        $hasDuplicates = DB::table('wallets')->select('provider', 'identifier')->groupBy('provider', 'identifier')->havingRaw('COUNT(*) > 1')->exists();

        if ($hasEmpty || $hasDuplicates) {
            throw new \RuntimeException('Rollback refused: wallets with empty fields or duplicate numbers exist.');
        }

        Schema::table('wallets', function (Blueprint $table): void {
            $table->string('name', 100)->nullable(false)->change();
            $table->string('provider', 30)->nullable(false)->change();
            $table->string('identifier', 64)->nullable(false)->change();
            $table->unique(['provider', 'identifier']);
        });
    }
};
