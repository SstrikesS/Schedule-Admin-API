<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Sanctum\PersonalAccessToken;
use App\Models\V1\Admin\AUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public ?AUser $user;

    protected function CurrentAUser(Request $request): ?AUser
    {
        $this->user = $request->user();

        return $this->user;
    }

    protected function CurrentAdminAccessToken(AUser $user): PersonalAccessToken
    {
        /** @var $token PersonalAccessToken */
        $token = $user->currentAccessToken();

        return $token;
    }

    protected function invalidAuthRes(): JsonResponse
    {
        return resJson([
            'code'  => 200, //400,
            'error' => [
                'user' => trans('v1/auth.error_username_not_exist')
            ]
        ]);
    }
}

