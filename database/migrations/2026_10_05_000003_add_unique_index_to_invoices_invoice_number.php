<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Invoice numbers are generated randomly and checked with exists(), which is not
 * race-proof on its own. A unique index makes a duplicate impossible at the
 * database level.
 */
return new class extends Migration
{
    private const INDEX = 'invoices_invoice_number_unique';

    public function up(): void
    {
        if (! Schema::hasTable('invoices') || ! Schema::hasColumn('invoices', 'invoice_number')) {
            return;
        }

        if ($this->hasUniqueIndex()) {
            return;
        }

        $hasDuplicates = DB::table('invoices')
            ->select('invoice_number')
            ->groupBy('invoice_number')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicates) {
            throw new \RuntimeException('Cannot add a unique index: duplicate invoice numbers already exist. Fix them first, then run migrate again.');
        }

        Schema::table('invoices', function (Blueprint $table): void {
            $table->unique('invoice_number', self::INDEX);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('invoices') && $this->hasIndexNamed(self::INDEX)) {
            Schema::table('invoices', function (Blueprint $table): void {
                $table->dropUnique(self::INDEX);
            });
        }
    }

    private function hasUniqueIndex(): bool
    {
        return collect(Schema::getIndexes('invoices'))->contains(
            fn (array $index) => ($index['unique'] ?? false) && ($index['columns'] ?? []) === ['invoice_number']
        );
    }

    private function hasIndexNamed(string $name): bool
    {
        return collect(Schema::getIndexes('invoices'))->contains(fn (array $index) => ($index['name'] ?? '') === $name);
    }
};
