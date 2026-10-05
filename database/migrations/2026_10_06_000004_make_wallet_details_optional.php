<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OPTIONAL_COLUMNS = [
        'name', 'provider', 'identifier', 'opening_balance', 'warn_at_percent',
        'default_commission_percent', 'default_commission_min', 'default_fee_percent',
        'default_fee_min', 'is_active',
    ];

    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table): void {
            $table->dropUnique('wallets_provider_identifier_unique');
            $table->string('name', 100)->nullable()->change();
            $table->string('provider', 30)->nullable()->change();
            $table->string('identifier', 64)->nullable()->change();
            $table->decimal('opening_balance', 14, 2)->nullable()->default(0)->change();
            $table->unsignedTinyInteger('warn_at_percent')->nullable()->default(80)->change();
            $table->decimal('default_commission_percent', 5, 2)->nullable()->default(0)->change();
            $table->decimal('default_commission_min', 10, 2)->nullable()->default(0)->change();
            $table->decimal('default_fee_percent', 5, 2)->nullable()->default(0)->change();
            $table->decimal('default_fee_min', 10, 2)->nullable()->default(0)->change();
            $table->decimal('default_fee_max', 10, 2)->nullable()->change();
            $table->boolean('is_active')->nullable()->default(true)->change();
        });
    }

    public function down(): void
    {
        foreach (self::OPTIONAL_COLUMNS as $column) {
            if (DB::table('wallets')->whereNull($column)->exists()) {
                throw new RuntimeException("Rollback refused: wallets.{$column} contains NULL values.");
            }
        }

        $hasDuplicateIdentifiers = DB::table('wallets')
            ->select('provider', 'identifier')
            ->whereNotNull('provider')
            ->whereNotNull('identifier')
            ->groupBy('provider', 'identifier')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateIdentifiers) {
            throw new RuntimeException('Rollback refused: duplicate wallet provider and identifier pairs exist.');
        }

        Schema::table('wallets', function (Blueprint $table): void {
            $table->string('name', 100)->nullable(false)->change();
            $table->string('provider', 30)->nullable(false)->change();
            $table->string('identifier', 64)->nullable(false)->change();
            $table->decimal('opening_balance', 14, 2)->nullable(false)->default(0)->change();
            $table->unsignedTinyInteger('warn_at_percent')->nullable(false)->default(80)->change();
            $table->decimal('default_commission_percent', 5, 2)->nullable(false)->default(0)->change();
            $table->decimal('default_commission_min', 10, 2)->nullable(false)->default(0)->change();
            $table->decimal('default_fee_percent', 5, 2)->nullable(false)->default(0)->change();
            $table->decimal('default_fee_min', 10, 2)->nullable(false)->default(0)->change();
            $table->decimal('default_fee_max', 10, 2)->nullable()->change();
            $table->boolean('is_active')->nullable(false)->default(true)->change();
            $table->unique(['provider', 'identifier']);
        });
    }
};
