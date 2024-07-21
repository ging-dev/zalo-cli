<?php

use Illuminate\Config\Repository;
use Illuminate\Support\Str;
use phpseclib3\Crypt\AES;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\BrowserKit\Response;

function aes(): AES
{
    return app(AES::class);
}

function browser(): HttpBrowser
{
    return app(HttpBrowser::class);
}

function browser_response(): Response
{
    return browser()->getResponse();
}

function imei(): string
{
    return Cache::rememberForever('imei', function (): string {
        return (string) Str::uuid();
    });
}

function zalo_params(): Repository
{
    return app('zalo');
}
