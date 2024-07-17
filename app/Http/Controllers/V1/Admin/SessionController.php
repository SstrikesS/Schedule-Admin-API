<?php

namespace App\Http\Controllers\V1\Admin;

use App\Consts\AccessTokenState;
use App\Consts\Schema\DBActivityFields;
use App\Events\Admin\AUserLogoutEvent;
use App\Http\Controllers\V1\BaseController;
use App\Libs\QueryFields;
use App\Models\Sanctum\PersonalAccessToken;
use App\Models\V1\Admin\ActivityModel;
use App\Structs\Struct;
use App\Structs\V1\Admin\APersonalAccessTokenStruct;
use App\Structs\V1\Admin\AUserActivityStruct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SessionController extends BaseController
{
    public function getSessions(Request $request): JsonResponse
    {
        $rule = [
            'sort'      => 'in:asc,desc',
            'sort_by'   => 'in:created_at,name,id',
            'search_by' => 'in:name,content',
        ];

        $message = [
            'sort_by'   => trans('v1/default.error_selected', ['field' => 'sort_by', 'option' => 'created_at,name,id']),
            'sort'      => trans('v1/default.error_selected', ['field' => 'sort', 'option' => 'asc, desc']),
            'search_by' => trans('v1/default.error_selected', ['field' => 'search_by', 'option' => 'name,content']),
        ];

        $validator = Validator::make($request->all(), $rule, $message);

        if ($validator->errors()->count()) {
            $json = [
                'error' => firstError($validator->getMessageBag()->toArray())
            ];
        } else {
            $items        = [];
            $user         = $this->CurrentAUser($request);
            $fields_token = new QueryFields($request, DBActivityFields::ACTIVITY);

            $filter_data = [
                'user_id'   => $user->getAttribute('id'),
                'select'    => $fields_token->select,
                'sort_by'   => $request->input('sort_by') ?? 'created_at',
                'sort'      => $request->input('sort') ?? 'desc',
                'search_by' => $request->input('search_by') ?? null,
                'key'       => "%{$request->input('key')}%" ?? '%%',
                ...pageLimit($request),
            ];

            $histories = ActivityModel::getUserLogin($request, $filter_data);

            foreach ($histories as $history) {

                $login_time = new AUserActivityStruct($history);

                $items[] = $login_time->toArray([
                    Struct::OPT_CHANGE => [
                        'user_agent' => ['handleDeviceAction'],
                    ],
                    Struct::OPT_FILTER => $fields_token->select,

                    Struct::OPT_IGNORE => [
                        'tokenable_id', 'id'
                    ],

                    Struct::OPT_EXTRA => [
                        'is_my_session' => ($login_time->id_session == self::CurrentAdminAccessToken($this->user)->getAttribute('id')) ?? null,
                        'state'         => PersonalAccessToken::find($login_time->id_session) != null ? PersonalAccessToken::find($login_time->id_session)->state : -1,
                    ]
                ]);
            }

            $json = [
                'items' => $items,
                '_meta' => ResMetaJson($histories)
            ];
        }
        return resJson($json);
    }

    public function destroySession(Request $request, $session_logout): JsonResponse
    {

        $user         = self::CurrentAUser($request);
        $this_session = self::CurrentAdminAccessToken($user)->getAttribute('id');

        $session_destroy = PersonalAccessToken::getSessionDestroy($session_logout);
        if ($session_destroy == null) {
            return resJson([
                'error' => [
                    'warning' => trans('v1/default.error_expires_session'),
                ],
                'code'  => 200,
            ]);
        } else {
            $session_destroy_struct = new APersonalAccessTokenStruct($session_destroy->getAttributes());

            if ($session_destroy_struct->id == $this_session) {
                return resJson([
                    'error' => [
                        'warning' => trans('v1/default.error_destroy_my_session'),
                    ],
                    'code'  => 200,
                ]);
            }
            if ($user->getAttribute('id') != $session_destroy_struct->tokenable_id) {
                return resJson([
                    'error' => [
                        'warning' => trans('v1/default.error_destroy_session_other_account'),
                    ],
                    'code'  => 200,
                ]);
            }
        }

        PersonalAccessToken::setExpiresToken($session_logout, AccessTokenState::OTHER_LOGOUT);
        AUserLogoutEvent::dispatch($this_session);

        $json = [
            'message' => 'success',
            'code'    => 200
        ];

        return resJson($json);
    }

    public function destroyAllSession(Request $request): JsonResponse
    {
        $user         = $this->CurrentAUser($request);
        $this_session = self::CurrentAdminAccessToken($user)->getAttribute('id');

        $sessions = PersonalAccessToken::getTokensLogin($user->getAttribute('id'));

        foreach ($sessions as $session) {
            if ($session->id == $this_session) {
                continue;
            }
            PersonalAccessToken::setExpiresToken($session->id, AccessTokenState::OTHER_LOGOUT);
            AUserLogoutEvent::dispatch($this_session);
        }

        return resJson();
    }
}
