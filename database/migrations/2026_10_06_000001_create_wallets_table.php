<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wallets')) {
            return;
        }

        Schema::create('wallets', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('provider', 30)->index();          // vodafone_cash, instapay, ...
            $table->string('identifier', 64);                 // wallet phone number or InstaPay address
            $table->string('holder_name', 100)->nullable();
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->decimal('balance', 14, 2)->default(0);    // maintained only by WalletLedgerService
            // Limits: NULL or 0 means "no limit". They differ per wallet tier and change over time.
            $table->decimal('per_transaction_limit', 14, 2)->nullable();
            $table->decimal('daily_send_limit', 14, 2)->nullable();
            $table->decimal('daily_receive_limit', 14, 2)->nullable();
            $table->decimal('monthly_send_limit', 14, 2)->nullable();
            $table->decimal('monthly_receive_limit', 14, 2)->nullable();
            $table->unsignedTinyInteger('warn_at_percent')->default(80);
            // Suggested values for the cashier form (always editable per transaction).
            $table->decimal('default_commission_percent', 5, 2)->default(0);
            $table->decimal('default_commission_min', 10, 2)->default(0);
            $table->decimal('default_fee_percent', 5, 2)->default(0);
            $table->decimal('default_fee_min', 10, 2)->default(0);
            $table->decimal('default_fee_max', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->unique(['provider', 'identifier']);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('wallet_transactions') && DB::table('wallet_transactions')->exists()) {
            throw new \RuntimeException('Rollback refused: wallet transactions exist. Wallets hold financial history.');
        }

        Schema::dropIfExists('wallets');
    }
};
