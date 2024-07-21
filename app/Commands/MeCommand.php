<?php

namespace App\Commands;

use App\Zalo;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class MeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:me';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check my information';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $zalo = Zalo::initialize();

        return static::SUCCESS;
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
