<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wallet_expenses')) {
            Schema::create('wallet_expenses', function (Blueprint $table): void {
                $table->id();
                $table->string('category', 60);
                $table->decimal('amount', 12, 2);
                $table->string('note', 500)->nullable();
                $table->date('spent_on')->index();
                $table->timestamp('voided_at')->nullable();
                $table->unsignedBigInteger('voided_by')->nullable();
                $table->string('void_reason', 255)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('created_by_name', 255)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('wallet_cash_adjustments')) {
            Schema::create('wallet_cash_adjustments', function (Blueprint $table): void {
                $table->id();
                $table->string('kind', 30);                  // opening, owner_deposit, owner_withdraw, correction
                $table->decimal('amount', 14, 2);            // signed
                $table->string('note', 500)->nullable();
                $table->dateTime('occurred_at')->index();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('created_by_name', 255)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('wallet_audit_logs')) {
            Schema::create('wallet_audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('user_name', 255)->nullable();
                $table->string('action', 60)->index();
                $table->string('subject_type', 40)->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->json('meta')->nullable();
                $table->string('ip', 45)->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['subject_type', 'subject_id']);
            });
        }
    }

    public function down(): void
    {
        foreach (['wallet_expenses', 'wallet_cash_adjustments', 'wallet_audit_logs'] as $name) {
            if (Schema::hasTable($name) && DB::table($name)->exists()) {
                throw new \RuntimeException("Rollback refused: {$name} contains financial records.");
            }
        }

        Schema::dropIfExists('wallet_audit_logs');
        Schema::dropIfExists('wallet_cash_adjustments');
        Schema::dropIfExists('wallet_expenses');
    }
};
