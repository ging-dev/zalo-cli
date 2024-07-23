<?php

namespace App\Commands;

use App\Zalo;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\table;

class FriendsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:friends';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get list friends';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $zalo = new Zalo();

        info('Your friends');

        table(
            headers: ['id', 'name', 'phone', 'gender'],
            rows: collect($zalo->getFriends())
                ->map(fn ($item): array => [
                    $item['userId'],
                    $item['displayName'],
                    $item['phoneNumber'],
                    $item['gender'] ? 'Female' : 'Male',
                ])->toArray(),
        );

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
