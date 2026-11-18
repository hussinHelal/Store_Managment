<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Some existing installations already contain this table from a manual or
        // earlier partial deployment. Extend that table instead of failing on create.
        if (Schema::hasTable('installation_appointments')) {
            $missing = [];
            foreach ([
                'service_type' => fn (Blueprint $table) => $table->string('service_type', 120)->default('صيانة عامة'),
                'service_details' => fn (Blueprint $table) => $table->text('service_details')->nullable(),
                'address' => fn (Blueprint $table) => $table->string('address', 255)->nullable(),
                'notes' => fn (Blueprint $table) => $table->text('notes')->nullable(),
                'completed_at' => fn (Blueprint $table) => $table->dateTime('completed_at')->nullable(),
            ] as $column => $definition) {
                if (! Schema::hasColumn('installation_appointments', $column)) {
                    $missing[$column] = $definition;
                }
            }
            if ($missing !== []) {
                Schema::table('installation_appointments', function (Blueprint $table) use ($missing): void {
                    foreach ($missing as $definition) {
                        $definition($table);
                    }
                });
            }

            return;
        }

        Schema::create('installation_appointments', function (Blueprint $table): void {
            $table->id();
            $table->string('customer_name', 160);
            $table->string('phone', 32);
            $table->string('device_model', 160);
            $table->string('service_type', 120);
            $table->text('service_details')->nullable();
            $table->dateTime('scheduled_at')->index();
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('scheduled')->index();
            $table->string('address', 255)->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['technician_id', 'scheduled_at', 'status']);
        });
    }

    public function down(): void
    {
        // Keep appointment records intact during rollback. This table may have
        // existed before this migration was introduced.
    }
};
