<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Spatie\Url\Url;

/**
 * @psalm-import-type FriendType from \App\Types\Zalo
 */
final class Zalo
{
    public const INITIALIZE_KEY = '3FC4F0D2AB50057BCE0D90D9187A22B1';

    public function __construct()
    {
        $aes = aes();
        $aes->setKey(static::INITIALIZE_KEY);

        $zaloParams = zalo_params();
        $imei = imei();

        $zcid = (string) str(collect([
            $zaloParams->get('type'),
            $imei,
            now()->unix(),
        ])->join(','))
            ->pipe($aes->encrypt(...))
            ->pipe(bin2hex(...))
            ->upper()
        ;

        $zcidExt = Str::random(12);

        $zaloParams->set('zcid', $zcid);
        $zaloParams->set('zcid_ext', $zcidExt);

        self::zalo_setup($zcid, $zcidExt);

        $zaloParams->set(
            'params',
            $this->encodeAES([
                'imei' => $imei,
            ])
        );

        $info = $this->get(
            'https://wpa.chat.zalo.me/api/login/getLoginInfo',
            $zaloParams->all(),
            'getserverinfo',
            false
        );

        $secret_key = (string) str(data_get($info, 'zpw_enk'))
            ->fromBase64()
        ;

        aes()->setKey($secret_key);
    }

    /**
     * @return FriendType[]
     */
    public function getFriends(): array
    {
        return $this->get(
            'https://tt-profile-wpa.chat.zalo.me/api/social/friend/getfriends',
            [
                'zpw_ver' => 636,
                'zpw_type' => 30,
                'params' => $this->encodeAES([
                    'count' => 20000,
                    'imei' => imei(),
                ]),
            ]
        );
    }

    /**
     * @param string[] $parameters
     *
     * @return mixed[]
     */
    private function get(
        string $uri,
        array $parameters = [],
        ?string $name = null,
        bool $rawurldecode = true,
    ): array {
        if (null !== $name) {
            ksort($parameters);

            $parameters['signkey'] = str('zsecure')
                ->append($name)
                ->append(join($parameters))
                ->pipe(md5(...))
                ->toString()
            ;
        }

        browser()->request(
            'GET',
            Url::fromString($uri)->withQueryParameters($parameters),
        );

        return $this->decodeAES($rawurldecode);
    }

    /**
     * @param (string|int)[] $params
     */
    private function encodeAES(array $params): string
    {
        return (string) str(json_encode($params))
            ->pipe(aes()->encrypt(...))
            ->toBase64()
        ;
    }

    /**
     * @return mixed[]
     */
    private function decodeAES(bool $rawurldecode = true): array
    {
        $data = data_get(browser_response()->toArray(), 'data');

        if ($rawurldecode) {
            $data = rawurldecode($data);
        }

        $json = str($data)->fromBase64()
            ->pipe(aes()->decrypt(...))
        ;

        return data_get(json_decode($json, true), 'data');
    }

    private static function zalo_setup(string $zcid, string $zcid_ext): void
    {
        /** @var callable(string): Collection<int,Stringable> */
        $processStr = static function (string $str): Collection {
            $evenOrOdd = collect(['', '']);
            foreach (str_split($str) as $k => $v) {
                $evenOrOdd[intval(0 === $k % 2)] .= $v;
            }

            return $evenOrOdd->map(str(...));
        };

        $a = $processStr(str($zcid_ext)
            ->pipe(md5(...))
            ->upper()
        )->last();

        $b = $processStr($zcid);

        $key = (string) $a->substr(0, 8)
            ->append($b->last()->substr(0, 12))
            ->append($b->first()->reverse()->substr(0, 12))
        ;

        aes()->setKey($key);
    }
}
