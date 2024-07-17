<?php

namespace App\Libs\Encrypt;

class EDFile
{
    public static function encrypt(string|array $value, ?string $secret = null): bool|string
    {
        $secret = $secret ?? env('ED_FILE_SECRET', '13245678913245678913245678913245');
        $iv = hex2bin(env('ED_FILE_IV', '13245678913245678913245678913245'));

        if (is_array($value)) {
            $value = json_encode($value);
        }

        // Store the cipher method
        $ciphering = "AES-256-CBC";

        // Use OpenSSl Encryption method
        $options = 0;

        return openssl_encrypt($value, $ciphering, $secret, $options, $iv);

//        $data = openssl_encrypt($value, $ciphering, $secret, $options, $iv);
//
//        if ($data) {
//            if (str_starts_with($value, '/')) {
//                $data[0] = '!';
//            }
//        }
//
//        return $data;
    }

    public static function decrypt($value, ?string $secret = null)
    {
//        if ($value) {
//            if (str_starts_with($value, '!')) {
//                $value[0] = '/';
//            }
//        }

        $secret = $secret ?? env('ED_FILE_SECRET', '13245678913245678913245678913245');
        $iv = hex2bin(env('ED_FILE_IV', '13245678913245678913245678913245'));

        $ciphering = "AES-256-CBC";

        // Use OpenSSl Encryption method
        $options = 0;

        $decryption = openssl_decrypt($value, $ciphering, $secret, $options, $iv);

        return json_decode($decryption, true);
    }

    public static function setLinkUrl($value): ?string
    {
        $enc = self::encrypt($value);

        if ($enc) {
            return base64_encode($enc);
        }

        return null;
    }

    public static function getLinkData($str): ?array
    {
        $decode = base64_decode($str);

        if ($decode) {
            return self::decrypt($decode);
        }

        return null;
    }
}
