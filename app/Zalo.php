<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

/**
 * @psalm-import-type FriendType from \App\Types\Zalo
 */
final class Zalo
{
    public const INITIALIZE_KEY = '3FC4F0D2AB50057BCE0D90D9187A22B1';

    /**
     * @param string $enk
     */
    private function __construct(string $enk) {
        aes()->setKey($enk);
    }

    public static function initialize(): static
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

        $zcid_ext = Str::random(12);

        $zaloParams->set('zcid', $zcid);
        $zaloParams->set('zcid_ext', $zcid_ext);

        static::zalo_setup($zcid, $zcid_ext);

        $zaloParams->set(
            'params',
            (string) str(json_encode(compact('imei')))
                ->pipe($aes->encrypt(...))
                ->toBase64()
        );

        browser()->request(
            'GET',
            sprintf(
                'https://wpa.chat.zalo.me/api/login/getLoginInfo?%s',
                static::zalo_build_query('getserverinfo', $zaloParams->all())
            ),
        );

        $user_data = json_decode(
            str(data_get(browser_response()->toArray(), 'data'))
                ->fromBase64()
                ->pipe($aes->decrypt(...)),
            true
        );

        $enk = (string) str(data_get($user_data, 'data.zpw_enk'))
            ->fromBase64()
        ;

        return new static($enk);
    }

    /**
     * @return FriendType[]
     */
    public function getFriends(): array
    {
        $uri = sprintf(
            'https://tt-profile-wpa.chat.zalo.me/api/social/friend/getfriends?%s',
            http_build_query([
                'zpw_ver' => 636,
                'zpw_type' => 30,
                'params' => $this->encodeAES([
                    'count' => 20000,
                    'imei' => imei(),
                ]),
            ])
        );

        browser()->request('GET', $uri);

        return $this->decodeAES();
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
    private function decodeAES(): array
    {
        $json = (string) str(data_get(browser_response()->toArray(), 'data'))
            ->pipe(rawurldecode(...))
            ->pipe(base64_decode(...))
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

    /**
     * @param string[] $data
     */
    public static function zalo_build_query(string $action, array $data): string
    {
        ksort($data);

        $data['signkey'] = md5('zsecure'.$action.join($data));

        return http_build_query($data);
    }
}
