<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (Artisan::call('app:sync-accounts') !== 0) {
            throw new RuntimeException(Artisan::output() ?: 'Built-in account synchronization failed.');
        }
    }
}
