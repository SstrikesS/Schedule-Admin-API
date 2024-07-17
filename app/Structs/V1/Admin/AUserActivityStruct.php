<?php

namespace App\Structs\V1\Admin;

use App\Libs\Serializer\Normalize;
use App\Structs\Struct;
use Illuminate\Support\Carbon;
use Jenssegers\Agent\Agent;

class AUserActivityStruct extends Struct
{
    public ?Carbon $created_at;
    public ?Carbon $updated_at;
    public ?string $data;
    public ?string $ip;
    public ?string $user_agent;
    public ?string $location;
    public ?string $id;
    public ?string $ref_id;
    public ?string $user_id;
    public ?string $key;
    public ?string $id_session;

    public function __construct(object|array $data)
    {
        if (is_object($data)) {
            $data = (array)$data;
        }

        $this->created_at = Normalize::initCarbon($data, 'created_at');
        $this->updated_at = Normalize::initCarbon($data, 'updated_at');
        $this->data = Normalize::initString($data, 'data');
        $this->ip = Normalize::initString($data, 'ip');
        $this->user_agent = Normalize::initString($data, 'user_agent');
        $this->location = Normalize::initString($data, 'location');
        $this->id = Normalize::initString($data, 'id');
        $this->ref_id = Normalize::initString($data, 'ref_id');
        $this->user_id = Normalize::initString($data, 'user_id');
        $this->key = Normalize::initString($data, 'key');
        $this->id_session = Normalize::initString($data, 'id_session');

    }

    public function handleDeviceAction(): array|string
    {
        $agent = new Agent();
        $agent->setUserAgent($this->user_agent);
        if ($agent->isRobot())
            return 'unknown';
        else
            return [
                'platform'         => $agent->platform() ?? null,
                'version platform' => $agent->version($agent->platform()) ?? null,
                'browser'          => $agent->browser() ?? null,
                'version browser'  => $agent->version($agent->browser()) ?? null,
                'device'           => $agent->device() ?? null,
            ];
    }
}
