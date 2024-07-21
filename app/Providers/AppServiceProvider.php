<?php

namespace App\Providers;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('pipeline', function (Container $container): Pipeline {
            return new Pipeline($container);
        });

        $this->app->singleton('zalo', function (): Repository {
            return new Repository(config('zalo'));
        });
    }
}
