<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use phpseclib3\Crypt\AES;

class AESProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AES::class, function (): AES {
            $aes = new AES('cbc');
            $aes->enablePadding();
            $aes->setIV(str(chr(0))->repeat(16)->toString());

            return $aes;
        });
    }
}
