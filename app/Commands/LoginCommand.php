<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

class LoginCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:login';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Zalo login';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->request('GET', 'account');
        $this->request('POST', 'account/authen/qr/generate');

        $resp = browser_response()->toArray();
        $base64image = data_get($resp, 'data.image');
        $code = data_get($resp, 'data.code');

        $base64ToBin = (string) str($base64image)
            ->chopStart('data:image/png;base64,')
            ->fromBase64();

        Storage::put('qr.png', $base64ToBin);

        $this->info('Waiting scan QR in application...');

        foreach (['scan', 'confirm'] as $action) {
            $this->request(
                'POST',
                sprintf('account/authen/qr/waiting-%s', $action),
                ['code' => $code]
            );
        }

        $this->request(
            'GET',
            sprintf(
                'account/checksession?%s', http_build_query([
                    'continue' => 'https://chat.zalo.me',
                ])
            )
        );

        $cookies = collect(browser()->getCookieJar()->all())
            ->map(strval(...));

        Storage::put('zalo.json', json_encode($cookies));

        $this->info('Done...');

        return static::SUCCESS;
    }

    /**
     * @param string[] $parameters
     */
    private function request(
        string $method,
        string $uri,
        array $parameters = []
    ): void {
        browser()->request(
            $method,
            sprintf('https://id.zalo.me/%s', $uri),
            $parameters,
        );
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
