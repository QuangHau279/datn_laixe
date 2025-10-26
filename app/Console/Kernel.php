<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /** 
     * Đăng ký thủ công các command (không bắt buộc nếu đã load() thư mục Commands)
     */
    protected $commands = [
        \App\Console\Commands\ImportTrafficSigns::class,  // dòng này OK
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Tự động nạp mọi command trong app/Console/Commands
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
