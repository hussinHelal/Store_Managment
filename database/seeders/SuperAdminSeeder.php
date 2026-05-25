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

        $defaultAdmins = [
            [
                'name'     => 'Super Administrator',
                'email'    => 'mohamed@mohamed.com',
                'password' => '01008129710',
            ],
            [
                'name'     => 'Super Administrator 2',
                'email'    => 'hussin@hussin.com',
                'password' => 'hussin98741',
            ],
        ];

        foreach ($defaultAdmins as $admin) {
            User::create([
                'name'     => $admin['name'],
                'email'    => $admin['email'],
                'password' => Hash::make($admin['password']),
                'role'     => 'superadmin',
            ]);

            $this->command->info('Created default superadmin: ' . $admin['email']);
        }

        $this->command->warn('Please change these passwords after first login!');
    }
}
