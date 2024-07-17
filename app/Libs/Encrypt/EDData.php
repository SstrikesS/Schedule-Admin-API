<?php

namespace App\Libs\Encrypt;

class EDData
{
    protected static function opts(): array
    {
        return [
            'secret' => env('ED_DATA_SECRET'),
        ];
    }

    public static function encrypt($value): array
    {
        $driver = new CryptoJsAes(self::opts());

        return $driver->encrypt($value);
    }

    public static function decrypt($value): array
    {
        $driver = new CryptoJsAes(self::opts());

        return $driver->decrypt($value);
    }

    public static function getData($str): ?array
    {
        $decode = base64_decode($str);

        if ($decode) {
            return self::decrypt($decode);
        }

        return null;
    }

    public static function setData($value): ?string
    {
        $enc = self::encrypt($value);

        if ($enc) {
            return base64_encode(json_encode($enc));
        }

        return null;
    }
}