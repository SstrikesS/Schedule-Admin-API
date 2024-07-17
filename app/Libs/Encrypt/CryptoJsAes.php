<?php

namespace App\Libs\Encrypt;

class CryptoJsAes
{
    protected string $secret;

    public function __construct(array $opts)
    {
        $this->secret = $opts['secret'] ?? '13245678913245678913245678913245';
    }

    public function encrypt($value, ?string $secret = null): array
    {
        $passphrase = $secret ?? $this->secret;

        $salt = openssl_random_pseudo_bytes(8);
        $salted = '';
        $dx = '';

        while (strlen($salted) < 48) {
            $dx = md5($dx . $passphrase . $salt, true);
            $salted .= $dx;
        }

        $key = substr($salted, 0, 32);
        $iv = substr($salted, 32, 16);

        $ciphering = "AES-256-CBC";

        $encrypted_data = openssl_encrypt(json_encode($value), $ciphering, $key, true, $iv);

        return [
            "c" => base64_encode($encrypted_data),
            "i" => bin2hex($iv),
            "s" => bin2hex($salt)
        ];
    }

    public function decrypt(string|array $json, ?string $secret = null): array
    {
        $passphrase = $secret ?? $this->secret;

        if (is_string($json)) {
            $json = json_decode($json, true);
        }

        if (!$json) {
            return [];
        }

        $ct = $json["c"];
        $iv = $json["i"];
        $salt = $json["s"];

        $salt = hex2bin($salt);
        $iv = hex2bin($iv);
        $ct = base64_decode($ct);
        $ciphering = "AES-256-CBC";

        $concatPassphrase = $passphrase . $salt;
        $md5 = [];
        $md5[0] = md5($concatPassphrase, true);
        $result = $md5[0];

        for ($i = 1; $i < 3; $i++) {
            $md5[$i] = md5($md5[$i - 1] . $concatPassphrase, true);
            $result .= $md5[$i];
        }

        $key = substr($result, 0, 32);
        $data = openssl_decrypt($ct, $ciphering, $key, true, $iv);

        return $data ? json_decode($data, true) : [];
    }
}