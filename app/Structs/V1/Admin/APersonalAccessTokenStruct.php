<?php

namespace App\Structs\V1\Admin;

use App\Libs\Serializer\Normalize;
use App\Structs\Struct;
use Illuminate\Support\Carbon;

class APersonalAccessTokenStruct extends Struct
{
    public ?Carbon $last_used_at;
    public ?Carbon $expires_at;
    public ?Carbon $created_at;
    public ?Carbon $updated_at;
    public ?string $token;
    public ?string $id;
    public ?string $abilities;
    public ?string $tokenable_type;
    public ?string $tokenable_id;
    public ?string $name;

    public function __construct(object|array $data)
    {
        if (is_object($data)) {
            $data = (array)$data;
        }
        $this->last_used_at   = Normalize::initCarbon($data, 'last_used_at');
        $this->expires_at     = Normalize::initCarbon($data, 'expires_at');
        $this->created_at     = Normalize::initCarbon($data, 'created_at');
        $this->updated_at     = Normalize::initCarbon($data, 'updated_at');
        $this->token          = Normalize::initString($data, 'token');
        $this->id             = Normalize::initString($data, 'id');
        $this->abilities      = Normalize::initString($data, 'abilities');
        $this->tokenable_type = Normalize::initString($data, 'tokenable_type');
        $this->tokenable_id   = Normalize::initString($data, 'tokenable_id');
        $this->name           = Normalize::initString($data, 'name');

    }

    public function lastLogin(): string
    {
        $string = null;

        $interval_time = now()->diff($this->created_at);
        if ($interval_time->y != 0) {
            $string = $string . $interval_time->y . ' year ';
        }
        if ($interval_time->m != 0) {
            $string = $string . $interval_time->m . ' month ';
        }
        if ($interval_time->d != 0) {
            $string = $string . $interval_time->d . ' day ';
        }
        if ($interval_time->h != 0) {
            $string = $string . $interval_time->h . ' hour ';
        }
        if ($interval_time->i != 0) {
            $string = $string . $interval_time->i . ' minute ';
        }
        if ($interval_time->s != 0) {
            $string = $string . $interval_time->s . ' second ';
        }
        return $string . 'ago';
    }
}
