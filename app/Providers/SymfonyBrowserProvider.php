<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\BrowserKit\HttpBrowser;

class SymfonyBrowserProvider extends ServiceProvider
{
    protected bool $defer = true;

    public function register(): void
    {
        $this->app->singleton(HttpBrowser::class, function (): HttpBrowser {
            $browser = new HttpBrowser();
            if (Storage::exists('zalo.json')) {
                $browser->getCookieJar()
                    ->updateFromSetCookie(Storage::json('zalo.json'));
            }

            return $browser;
        });
    }
}
