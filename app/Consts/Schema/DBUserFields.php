<?php

namespace App\Consts\Schema;

use App\Consts\DbTypes;

abstract class DBUserFields
{
    const USERS
        = [
            'id'         => [
                'type'  => DbTypes::STRING,
                'cache' => true
            ],
            'name'       => [
                'type'  => DbTypes::STRING,
                'cache' => true
            ],
            'username'   => [
                'type'  => DbTypes::STRING,
                'cache' => true
            ],
            'email'      => [
                'type'  => DbTypes::STRING,
                'cache' => true
            ],
            'password'   => [
                'type'  => DbTypes::STRING,
                'cache' => true
            ],
            'image'      => [
                'type'  => DbTypes::STRING,
                'cache' => true
            ],
            'status'     => [
                'type'  => DbTypes::INT,
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
            'phone'      => [
                'type'  => DbTypes::STRING,
                'cache' => true
            ],
            'address'    => [
                'type'  => DbTypes::STRING,
                'cache' => true
            ],
            'DoB'        => [
                'type'  => DbTypes::STRING,
                'cache' => true,
            ]
        ];
}
