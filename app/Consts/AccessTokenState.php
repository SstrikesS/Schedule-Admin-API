<?php

namespace App\Consts;

abstract class AccessTokenState
{
    const ACTIVE = 1;
    const MY_LOGOUT = 0;
    const OTHER_LOGOUT = 2;
}
