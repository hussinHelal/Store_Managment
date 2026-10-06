<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only ledger. Rows are never edited or deleted; mistakes are fixed with
 * a reversal row. wallet balance = opening_balance + SUM(wallet_delta).
 * Cash in drawer = SUM(cash_delta) + cash adjustments - expenses.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wallet_transactions')) {
            return;
        }

        Schema::create('wallet_transactions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('wallet_id')->constrained('wallets')->restrictOnDelete();
            $table->string('type', 20);                          // send, receive, reversal, settlement, adjustment
            $table->string('status', 20)->default('completed');  // completed, reversed
            $table->decimal('amount', 14, 2)->default(0);        // principal moved through the wallet
            $table->decimal('commission', 12, 2)->default(0);    // earned from the customer
            $table->decimal('fee', 12, 2)->default(0);           // charged to the wallet by the provider
            $table->decimal('profit', 12, 2)->default(0);        // commission - fee
            $table->decimal('wallet_delta', 14, 2)->default(0);  // signed effect on wallet balance
            $table->decimal('cash_delta', 14, 2)->default(0);    // signed effect on cash drawer
            $table->decimal('balance_after', 14, 2)->default(0); // wallet balance after this row
            $table->string('payment_method', 20)->default('cash'); // cash | deferred
            $table->decimal('receivable', 14, 2)->default(0);    // still owed by the customer
            $table->timestamp('settled_at')->nullable();
            $table->string('counterparty', 64)->nullable();      // recipient / sender number
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_name', 120)->nullable();
            $table->string('notes', 500)->nullable();
            $table->foreignId('reverses_id')->nullable()->constrained('wallet_transactions')->restrictOnDelete();
            $table->foreignId('settles_id')->nullable()->constrained('wallet_transactions')->restrictOnDelete();
            $table->timestamp('reversed_at')->nullable();
            $table->unsignedBigInteger('reversed_by')->nullable();
            $table->string('reversal_reason', 255)->nullable();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->dateTime('occurred_at')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('created_by_name', 255)->nullable();
            $table->timestamps();

            $table->index(['wallet_id', 'occurred_at']);
            $table->index(['type', 'status', 'occurred_at']);
            $table->index(['payment_method', 'receivable']);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('wallet_transactions') && DB::table('wallet_transactions')->exists()) {
            throw new \RuntimeException('Rollback refused: the wallet ledger contains financial records.');
        }

        Schema::dropIfExists('wallet_transactions');
    }
};
