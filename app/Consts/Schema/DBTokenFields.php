<?php

namespace App\Consts\Schema;

use App\Consts\DbTypes;
use App\Consts\DateFormat;

abstract class DBTokenFields
{
    const TOKEN = [
        'id'         => [
            'type'  => DbTypes::STRING,
            'cache' => true
        ],
        'tokenable_type'       => [
            'type'  => DbTypes::STRING,
            'cache' => true
        ],
        'tokenable_id'   => [
            'type'  => DbTypes::STRING,
            'cache' => true
        ],
        'name'      => [
            'type'  => DbTypes::STRING,
            'cache' => true
        ],
        'token'   => [
            'type'  => DbTypes::STRING,
            'cache' => true
        ],
        'abilities'      => [
            'type'  => DbTypes::STRING,
            'cache' => true
        ],
        'last_used_at'     => [
            'type'  => DbTypes::STRING,
            'cache' => true
        ],
        'expires_at'   => [
            'type'  => DbTypes::STRING,
            'cache' => true
        ],
        'created_at' => [
            'type'  => DbTypes::STRING,
            'cache' => true
        ],
        'updated_at' => [
            'type'  => DbTypes::STRING,
            'cache' => false
        ],
    ];
}
