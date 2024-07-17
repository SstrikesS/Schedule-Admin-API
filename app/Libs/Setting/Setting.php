<?php

namespace App\Libs\Setting;

class Setting
{
    public ?DataClient $dataClient = null;
    public BaseObject $store;

    public function __construct()
    {
        $this->store = new BaseObject();
    }
}