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
        // Check if a superadmin already exists
        if (User::where('role', 'superadmin')->exists()) {
            $this->command->info('Superadmin user already exists.');
            return;
        }

        // Create a default superadmin
        User::create([
            'name'     => 'Super Administrator',
            'email'    => 'superadmin@storemanagement.local',
            'password' => Hash::make('SuperAdmin@123'),
            'role'     => 'superadmin',
        ]);

        $this->command->info('Superadmin user created successfully!');
        $this->command->info('Email: superadmin@storemanagement.local');
        $this->command->info('Password: SuperAdmin@123');
        $this->command->warn('Please change the password after first login!');
    }
}
