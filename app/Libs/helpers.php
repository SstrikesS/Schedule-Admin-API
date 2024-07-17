<?php

use App\Consts\DbTypes;
use App\Libs\Encrypt\EDData;
use App\Libs\Setting\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

//use Godruoyi\Snowflake\Snowflake;

function ddev(...$vars): void
{
    if (config('app.debug')) {
        dd($vars);
    }
}

function runningInOctane(): bool
{
    return !app()->runningInConsole() && env('LARAVEL_OCTANE');
}

//if (!function_exists('snowflake')) {
//    /**
//     * Arrange for a agent
//     */
//    function snowflake(): Snowflake
//    {
//        return app('snowflake');
//    }
//}

if (!function_exists('setting')) {
    /**
     * Arrange for a agent
     */
    function setting(): Setting
    {
        return app('setting');
    }
}

function generateUlid(): string
{
    return Str::lower(Str::ulid()->toBase32());
}

function cdn_gs_asset($path, ?string $sub = null): string
{
    if (!$sub) {
        $sub = 'v' . config('web.cdn_gs.v');
    }

    return config('web.cdn_gs.url') . "/$sub/$path";
}

function cdn_sc_asset($path): string
{
    return config('web.cdn_sc.url') . "/$path";
}

function cdn_sc_path($path): string
{
    return config('web.cdn_sc.url') . "/$path";
}

function timeAgo($start, $end): string
{
    $diff = strtotime($end) - strtotime($start);

    // Time difference in seconds
    $sec = $diff;

    // Convert time difference in minutes
    $min = round($diff / 60, 2);

    // Convert time difference in hours
    $hrs = round($diff / 3600, 2);

    // Convert time difference in days
    $days = round($diff / 86400, 2);

    // Convert time difference in weeks
    $weeks = round($diff / 604800, 2);

    // Convert time difference in months
    $mnths = round($diff / 2600640, 2);

    // Convert time difference in years
    $yrs = round($diff / 31207680, 2);

    // Check for seconds
    if ($sec <= 60) {
        return "$sec seconds";
    } else if ($min <= 60) {
        if ($min == 1) {
            return "1 minute";
        } else {
            return "$min minutes";
        }
    } else if ($hrs <= 24) {
        if ($hrs == 1) {
            return "1 hour";
        } else {
            return "$hrs hours";
        }
    } else if ($days <= 7) {
        if ($days == 1) {
            return "Yesterday";
        } else {
            return "$days days";
        }
    } else if ($weeks <= 4.3) {
        if ($weeks == 1) {
            return "1 week";
        } else {
            return "$weeks weeks";
        }
    } else if ($mnths <= 12) {
        if ($mnths == 1) {
            return "1 month";
        } else {
            return "$mnths months";
        }
    } else {
        if ($yrs == 1) {
            return "1 year";
        } else {
            return "$yrs years";
        }
    }
}

function hed($string, $quote_style = ENT_QUOTES, $charset = 'utf-8'): string
{
    return html_entity_decode($string, $quote_style, $charset);
}

function formatByte($num, $precision = 1): string
{
    $num = (int)$num;
    $i = 0;
    $suffix = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    while (($num / 1024) > 1) {
        $num = $num / 1024;
        $i++;
    }

    return round(mb_substr($num, 0, mb_strpos($num, '.') + 4), $precision) . $suffix[$i];
}

/**
 * Get the first error.
 */
function firstError(array $error): array
{
    $new = [];

    foreach ($error as $key => $item) {
        $new[$key] = $item[0];
    }

    return $new;
}

function resJson($data = [], bool $is_clear = true): JsonResponse
{
    if (!is_array($data)) {
        if (setting()->dataClient?->isEncrypt()) {
            return response()->json(EDData::setData($data), SymfonyResponse::HTTP_OK);
        } else {
            return response()->json($data, SymfonyResponse::HTTP_OK);
        }
    }

    if (isset($data['code'])) {
        $code = $data['code'];
    } else {
        $code = SymfonyResponse::HTTP_OK;

        $data['code'] = $code;
    }

    if (isset($data['error'])) {
        $success = false;
    } else {
        $success = true;
    }

    $data['success'] = $success;

    if ($is_clear) {
        $data = rmArrayObjectByValue($data);
    }
    if (setting()->dataClient?->isEncrypt()) {
        return response()->json(EDData::setData($data), $code);
    } else {
        return response()->json($data, $code);
    }
}

function formatJsonToSQL($value): string
{
    if (Str::isJson($value)) {
        return $value;
    }

    return json_encode($value && is_array($value) ? $value : []);
}

function normalizeToRequest(array $input, array $compare): array
{
    $data = [];

    foreach ($input as $key => $value) {
        if (!isset($compare[$key])) {
            continue;
        }

        if (!is_null($value)) {
            switch ($compare[$key]['type']) {
                case DbTypes::STRING:
                    if (!is_string($value)) {
                        $value = strval($value);
                    } else {
                        $value = trim($value);
                    }

                    break;
                case DbTypes::INT:
                    if (!is_int($value)) {
                        $value = intval($value);
                    }

                    break;
                case DbTypes::FLOAT:
                    if (!is_float($value)) {
                        $value = floatval($value);
                    }

                    break;
                case DbTypes::BOOL:
                    if (!is_bool($value)) {
                        if (is_string($value)) {
                            if ($value === 'true') {
                                $value = true;
                            } elseif ($value === '1') {
                                $value = true;
                            } elseif ($value === 'false') {
                                $value = false;
                            } elseif ($value === '0') {
                                $value = false;
                            } else {
                                $value = boolval($value);
                            }
                        } elseif (is_int($value)) {
                            if ($value === 1) {
                                $value = true;
                            } elseif ($value === 0) {
                                $value = false;
                            } else {
                                $value = boolval($value);
                            }
                        } elseif (is_array($value)) {
                            $value = count($value) > 0;
                        } else {
                            $value = boolval($value);
                        }
                    }

                    break;
                case DbTypes::JSON:
                    if (
                        is_string($value)
                        && Str::isJson($value)
                    ) {
                        $value = json_decode($value, true);
                    }

                    break;
            }
        }

        $data[$key] = $value;
    }

    return $data;
}

/**
 * So sánh dữ liệu post với các trường của DB được định nghĩa sẵn
 * - Nếu so với được định nghĩa không có thì clear
 */
function normalizeToSQLViaArray(array $input, array $compare): array
{
    $data = [];

    foreach ($input as $key => $value) {
        if (!isset($compare[$key])) {
            continue;
        }

        if (!is_null($value)) {
            switch ($compare[$key]['type']) {
                case DbTypes::STRING:
                    if (!is_string($value)) {
                        $value = strval($value);
                    } else {
                        $value = trim($value);
                    }

                    break;
                case DbTypes::INT:
                    if (!is_int($value)) {
                        $value = intval($value);
                    }

                    break;
                case DbTypes::FLOAT:
                    if (!is_float($value)) {
                        $value = floatval($value);
                    }

                    break;
                case DbTypes::BOOL:
                    if (!is_bool($value)) {
                        if (is_string($value)) {
                            if ($value === 'true') {
                                $value = true;
                            } elseif ($value === '1') {
                                $value = true;
                            } elseif ($value === 'false') {
                                $value = false;
                            } elseif ($value === '0') {
                                $value = false;
                            } else {
                                $value = boolval($value);
                            }
                        } elseif (is_int($value)) {
                            if ($value === 1) {
                                $value = true;
                            } elseif ($value === 0) {
                                $value = false;
                            } else {
                                $value = boolval($value);
                            }
                        } elseif (is_array($value)) {
                            $value = count($value) > 0;
                        } else {
                            $value = boolval($value);
                        }
                    }

                    break;
                case DbTypes::JSON:
                    $value = formatJsonToSQL($value);

                    break;
            }
        }

        $data[$key] = $value;
    }

    return $data;
}

/**
 * So sánh dữ liệu post với dữ liệu đang được lưu trong DB
 * - Nếu dữ liệu giống với DB thi clear vì không có sự thay đổi
 */
function normalizeToSQLViaDB(array $input, array|object $compare): array
{
    if (is_object($compare)) {
        $compare = (array)$compare;
    }

    $data = [];

    foreach ($input as $key => $value) {
        if (!in_array($key, array_keys($compare))) {
            continue;
        }

        if (is_array($value)) {
            $value = formatJsonToSQL($value);
        } elseif (is_string($value)) {
            $value = trim($value);
        }

        if (gettype($value) != gettype($compare[$key])) {
            switch (gettype($compare[$key])) {
                case 'string':
                    $value = strval($value);

                    if ($value == '') {
                        $value = null;
                    }

                    break;
                case 'integer':
                    $value = intval($value);

                    break;
                case 'double':
                    $value = floatval($value);

                    break;
                case 'boolean':
                    if (is_string($value)) {
                        if ($value === 'true') {
                            $value = true;
                        } elseif ($value === '1') {
                            $value = true;
                        } elseif ($value === 'false') {
                            $value = false;
                        } elseif ($value === '0') {
                            $value = false;
                        } else {
                            $value = boolval($value);
                        }
                    } elseif (is_int($value)) {
                        if ($value === 1) {
                            $value = true;
                        } elseif ($value === 0) {
                            $value = false;
                        } else {
                            $value = boolval($value);
                        }
                    } elseif (is_array($value)) {
                        $value = count($value) > 0;
                    } else {
                        $value = boolval($value);
                    }

                    break;
            }
        }

        if ($value != $compare[$key]) {
            $data[$key] = $value;
        }
    }

    return $data;
}

function normalizeToRedisViaArray(array|object $input, array $compare): array
{
    if (is_object($input)) {
        $input = (array)$input;
    }

    $data = [];

    foreach ($input as $key => $value) {
        if (!isset($compare[$key])) {
            continue;
        }

        $field = $compare[$key];

        if (!isset($field['cache']) || !$field['cache']) {
            continue;
        }

        switch ($field['type']) {
            case DbTypes::STRING:
                if (!is_string($value)) {
                    $value = strval($value);
                } else {
                    $value = trim($value);
                }

                break;
            case DbTypes::INT:
                if (!is_int($value)) {
                    $value = intval($value);
                }

                break;
            case DbTypes::FLOAT:
                if (!is_float($value)) {
                    $value = floatval($value);
                }

                break;
            case DbTypes::BOOL:
                if (!is_bool($value)) {
                    if (is_string($value)) {
                        if ($value === 'true') {
                            $value = true;
                        } elseif ($value === '1') {
                            $value = true;
                        } elseif ($value === 'false') {
                            $value = false;
                        } elseif ($value === '0') {
                            $value = false;
                        } else {
                            $value = boolval($value);
                        }
                    } elseif (is_int($value)) {
                        if ($value === 1) {
                            $value = true;
                        } elseif ($value === 0) {
                            $value = false;
                        } else {
                            $value = boolval($value);
                        }
                    } elseif (is_array($value)) {
                        $value = count($value) > 0;
                    } else {
                        $value = boolval($value);
                    }
                }

                break;
            case DbTypes::JSON:
                if (is_string($value)) {
                    if (!$value = json_decode($value, true)) {
                        $value = [];
                    }
                } elseif (!is_array($value)) {
                    $value = [];
                }

                break;
        }

        $data[$key] = $value;
    }

    return $data;
}

function rmArrayObjectByValue(array|object $arr, mixed $compare = [null, '']): array|object
{
    $is_object = is_object($arr);

    $data = [];

    foreach ((array)$arr as $key => $value) {
        if (is_array($value)) {
            if (!$value) {
                continue;
            }
            $data[$key] = rmArrayObjectByValue($value, $compare);
        } else {
            if (is_array($compare)) {
                if (in_array($value, $compare, true)) {
                    continue;
                }
            } else {
                if ($value === $compare) {
                    continue;
                }
            }

            $data[$key] = $value;
        }
    }

    if ($is_object) {
        return (object)$data;
    }

    return $data;
}

// remove phan tu trong array hay object bang value(gia tri)
function rmArrayObjectByKey(array|object $arr, mixed $compare): array|object
{
    $is_object = is_object($arr);

    $data = array_filter((array)$arr, function ($key) use ($compare) {
        if (is_array($compare)) {
            return !in_array($key, $compare, true);
        } else {
            return $key !== $compare;
        }
    }, ARRAY_FILTER_USE_KEY);

    if ($is_object) {
        return (object)$data;
    }

    return $data;
}

//ham remove phan tu trong array hoac object theo key

function classConstKeyExists($class, $name): bool
{
    if (is_object($class) || is_string($class)) {
        try {
            $reflect = new ReflectionClass($class);

            return array_key_exists($name, $reflect->getConstants());
        } catch (ReflectionException) {
            //
        }
    }

    return false;
}

function classConstValueExists($class, $name): bool
{
    if (is_object($class) || is_string($class)) {
        try {
            $reflect = new ReflectionClass($class);

            return in_array($name, array_values($reflect->getConstants()));
        } catch (ReflectionException) {
            //
        }
    }

    return false;
}

function mergeObjOrArr(object|array $from, object|array $merge): object|array
{
    if (is_object($from)) {
        foreach ($merge as $key => $value) {
            $from->{$key} = $value;
        }
    } elseif (is_array($from)) {
        if (is_object($merge)) {
            $merge = (array)$merge;
        }

        $from = [
            ...$from,
            ...$merge
        ];
    }

    return $from;
}

function getInfoFromStoreOrQuery(array &$store, string|int $key, array $opts): array
{
    $data = [];

    if (isset($store[$key])) {
        $data = $store[$key];
    } else {
        if (!empty($opts['class'])) {
            if ($opts['class'][0] instanceof Collection) {
                $func_params = [];

                if (isset($opts['params'])) {
                    $func_params = [
                        ...$func_params,
                        ...$opts['params']
                    ];
                }
            } else {
                $func_params = [$key, $opts['fields']];

                if (isset($opts['params'])) {
                    $func_params = [
                        ...$func_params,
                        $opts['params']
                    ];
                }
            }

            $info = call_user_func($opts['class'], ...$func_params);
        }

        if (!empty($info)) {
            if (is_object($info)) {
                $info = (array)$info;
            }

            foreach ($opts['fields'] as $field) {
                if (isset($info[$field])) {
                    $data[$field] = $info[$field];
                }
            }

            if (!empty($opts['before']) && is_array($opts['before'])) {
                $data = [
                    ...$opts['before'],
                    ...$data
                ];
            }

            if (!empty($opts['after']) && is_array($opts['after'])) {
                $data = [
                    ...$data,
                    ...$opts['after']
                ];
            }

            $store[$key] = $data;
        }
    }

    return $data;
}

function array_diff_assoc_recursive($array1, $array2): array
{
    $difference = [];

    foreach ($array1 as $key => $value) {
        if (is_array($value)) {
            if (!isset($array2[$key]) || !is_array($array2[$key])) {
                $difference[$key] = $value;
            } else {
                $new_diff = array_diff_assoc_recursive($value, $array2[$key]);

                if (!empty($new_diff)) {
                    $difference[$key] = $new_diff;
                }
            }
        } else if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
            $difference[$key] = $value;
        }
    }

    return $difference;
}

function ResMetaJson(mixed $data): ?array
{
    if ($data instanceof LengthAwarePaginator) {
        return [
            'per_page'     => $data->perPage(),
            'total_count'  => $data->total(),
            'page_count'   => $data->lastPage(),
            'current_page' => $data->currentPage(),
            'next_page'    => ($data->lastPage() > $data->currentPage()) ? ($data->currentPage() + 1) : null
        ];
    } else {

        return null;
    }
}

function pageLimit(Request $request): ?array
{
    if (!empty($request['limit'])) {

        return [
            'limit' => intval(trim($request->input('limit'), "'")),
            'page'  => intval(trim($request->input('page'), "'") ?? 1),
        ];
    } else {
        return [];
    }
}

