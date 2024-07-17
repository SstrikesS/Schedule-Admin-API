<?php


namespace App\Consts\Schema;

use App\Consts\DbTypes;
use App\Consts\DateFormat;

abstract class DBActivityFields
{
    const ACTIVITY = [
        'id' => [
            'type' => DbTypes::STRING,
            'cache' => true
        ],
        'user_id' => [
            'type' => DbTypes::STRING,
            'cache' => true
        ],
        'key' => [
            'type' => DbTypes::STRING,
            'cache' => true
        ],
        'data' => [
            'type' => DbTypes::JSON,
            'cache' => true
        ],
        'ip' => [
            'type' => DbTypes::STRING,
            'cache' => true
        ],
        'user_agent' => [
            'type' => DbTypes::STRING,
            'cache' => true
        ],
        'location' => [
            'type' => DbTypes::STRING,
            'cache' => true
        ],
        'created_at' => [
            'type' => DbTypes::STRING,
            'cache' => true
        ],
        'updated_at' => [
            'type' => DbTypes::STRING,
            'cache' => false
        ],
        'ref_id' => [
            'type' => DbTypes::STRING,
            'cache' => true
        ],
        'id_session' => [
            'type' => DbTypes::STRING,
            'cache' => true
        ],
    ];

}
