<?php

namespace App\Libs\Serializer;

use Illuminate\Support\Carbon;

/**
 * Chuẩn hóa dữ liệu được lấy ra từ database hoặc 1 mảng nào đó
 * Hỗ trợ nhiều định dạng dữ liệu khác nhau
 */
class Normalize
{
    public static function initArray(array $data, string $key, ?array $default = null, ?callable $callback = null): mixed
    {
        if (!isset($data[$key]) || !$data[$key]) {
            return $default;
        }

        $_data = is_array($data[$key])
            ? $data[$key]
            : json_decode($data[$key], true);

        if (!$_data || !is_array($_data)) {
            return $default;
        }

        return $callback ? $callback($_data) : $_data;
    }

    public static function initObject(
        array                  $data,
        string                 $key,
        ?array                 $default = null,
        ?callable              $callback = null,
        ?DenormalizerInterface $denormalizer = null
    ): mixed
    {
        $_data = self::initArray($data, $key);

        if (!$_data) {
            return $default;
        }

        if (is_array($_data)) {
            $_data = (object)$_data;
        }

        if ($denormalizer && method_exists($denormalizer, 'resolver')) {
            $denormalizer->resolver($_data);
        }

        return $callback ? $callback($_data) : $_data;
    }

    public static function initString(array $data, string $key, ?string $default = null, ?callable $callback = null): ?string
    {
        if (!isset($data[$key])) {
            return $default;
        }

        $_data = is_numeric($data[$key])
            ? strval($data[$key])
            : $data[$key];

        if (!is_string($_data)) {
            return $default;
        }

        return $callback ? $callback($_data) : $_data;
    }

    public static function initInt(array $data, string $key, ?int $default = null, ?callable $callback = null): ?int
    {
        if (!isset($data[$key])) {
            return $default;
        }

        if (is_string($data[$key])) {
            $_data = intval($data[$key]);
        } elseif (is_bool($data[$key])) {
            $_data = $data[$key] ? 1 : 0;
        } else {
            $_data = $data[$key];
        }

        if (!is_int($_data)) {
            return $default;
        }

        return $callback ? $callback($_data) : $_data;
    }

    public static function initFloat(array $data, string $key, ?float $default = null, ?callable $callback = null): ?float
    {
        if (!isset($data[$key])) {
            return $default;
        }

        $_data = is_string($data[$key])
            ? floatval($data[$key])
            : $data[$key];

        if (!is_float($_data)) {
            return $default;
        }

        return $callback ? $callback($_data) : $_data;
    }

    public static function initBool(array $data, string $key, ?bool $default = null, ?callable $callback = null): ?bool
    {
        if (!isset($data[$key])) {
            return $default;
        }

        if (is_string($data[$key]) || is_int($data[$key])) {
            $_data = match ($data[$key]) {
                1, '1', 'true' => true,
                0, '0', 'false' => false,
                default => $data[$key]
            };
        } else {
            $_data = $data[$key];
        }

        if (!is_bool($_data)) {
            return $default;
        }

        return $callback ? $callback($_data) : $_data;
    }

    public static function initCarbon(array $data, string $key, ?Carbon $default = null, ?callable $callback = null): ?Carbon
    {
        if (!isset($data[$key]) || !$data[$key]) {
            return $default;
        }

        if (is_numeric($data[$key])) {
            $_data = Carbon::createFromTimestamp($data[$key]);
        } elseif (is_string($data[$key])) {
            $_data = Carbon::parse($data[$key]);
        }

        if (!isset($_data) || !($_data instanceof Carbon)) {
            return $default;
        }

        return $callback ? $callback($_data) : $_data;
    }
}