<?php

namespace App\Libs\Serializer;

interface DenormalizerInterface
{
    public function resolver(mixed &$data);
}