<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Artisan::call('app:sync-accounts') !== 0) {
            throw new RuntimeException(Artisan::output() ?: 'Built-in account synchronization failed.');
        }
    }
}
