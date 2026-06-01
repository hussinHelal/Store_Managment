<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
    ];

    protected function schedule(Schedule $schedule)
    {
        // Automatic backup every month on the 1st at 2 AM
        $schedule->command('backup:database')->monthlyOn(1, '02:00')->withoutOverlapping();

        // Clean up old backup files (keep only last 12 backups)
        $schedule->call(function () {
            $backupDir = storage_path('app/backups');
            if (is_dir($backupDir)) {
                $files = array_diff(scandir($backupDir, SCANDIR_SORT_DESCENDING), ['.', '..']);
                $files = array_slice($files, 12); // Keep only last 12
                foreach ($files as $file) {
                    @unlink($backupDir . DIRECTORY_SEPARATOR . $file);
                }
            }
        })->monthlyOn(2, '03:00'); // Run cleanup on 2nd at 3 AM
    }

    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
