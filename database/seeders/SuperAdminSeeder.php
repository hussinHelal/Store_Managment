<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // If any superadmin already exists, do not create default accounts again.
        if (User::where('role', 'superadmin')->exists()) {
            $this->command->info('Superadmin user already exists.');
            return;
        }

        // No default superadmin accounts are created.
        // Client should create their own admin account through the registration process.
        $this->command->info('No default superadmin accounts created. Please register your first admin user.');
    }
}
