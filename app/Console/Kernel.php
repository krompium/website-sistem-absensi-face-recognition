<?php
// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check daily attendance at 10:00 AM
        $schedule->command('attendance:check-daily')
            ->dailyAt('10:00')
            ->emailOutputOnFailure(config('app.admin_email'));

        // Send daily reports to all parents at 3:00 PM
        $schedule->command('attendance:send-daily-reports')
            ->dailyAt('15:00')
            ->emailOutputOnFailure(config('app.admin_email'));

        // Database backup every day at 1:00 AM
        $schedule->command('db:backup')
            ->dailyAt('01:00')
            ->emailOutputOnFailure(config('app.admin_email'));

        // Clean old data monthly (keep last 365 days)
        $schedule->command('data:cleanup --days=365')
            ->monthlyOn(1, '02:00')
            ->emailOutputOnFailure(config('app.admin_email'));

        // Process pending notifications every 5 minutes
        $schedule->command('queue:work --stop-when-empty')
            ->everyFiveMinutes()
            ->withoutOverlapping();

        // Clear expired sessions daily
        $schedule->command('session:gc')
            ->daily();

        // Optimize application weekly
        $schedule->command('optimize')
            ->weekly()
            ->sundays()
            ->at('03:00');

        // ==========================================
        // UPDATE BARU: JADWAL LAPORAN MINGGUAN
        // ==========================================
        $schedule->command('attendance:send-weekly-reports')
            ->weeklyOn(5, '15:00') // 5 = Jumat, 15:00 = Jam 3 Sore
            ->timezone('Asia/Jakarta')
            ->emailOutputOnFailure(config('app.admin_email'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}