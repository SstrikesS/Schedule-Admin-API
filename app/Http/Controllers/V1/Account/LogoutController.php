<?php

namespace App\Http\Controllers\V1\Account;

use App\Consts\AccessTokenState;
use App\Events\Admin\AUserLogoutEvent;
use App\Http\Controllers\V1\BaseController;
use App\Models\Sanctum\PersonalAccessToken;
use App\Models\V1\Admin\AUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $user = self::CurrentAUser($request);

        if ($user instanceof AUser) {
            $id_session = self::CurrentAdminAccessToken($user)->getAttribute('id');

            PersonalAccessToken::setExpiresToken($id_session, AccessTokenState::MY_LOGOUT);

            AUserLogoutEvent::dispatch($id_session);
            $json = [];
        } else {
            $json = [
                'code'  => 200, //400,
                'error' => [
                    'user' => trans('v1/auth.error_username_not_exist'),
                ]
            ];
        }

        return resJson($json);
    }
}
