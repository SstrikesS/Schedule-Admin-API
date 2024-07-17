<?php

namespace App\Http\Controllers\V1\CUser;

use App\Consts\Schema\DBUserFields;
use App\Events\User\AdminDeleteUserEvent;
use App\Http\Controllers\V1\BaseController;
use App\Libs\QueryFields;
use App\Models\V1\User\CUser;
use App\Structs\Struct;
use App\Structs\V1\User\CUserStruct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class CUserController extends BaseController
{
    public function getUsers(Request $request): JsonResponse
    {
        $fields = new QueryFields($request, DBUserFields::USERS);

        $filter_name = $request->query('filter_name');
        $filter_username = $request->query('filter_username');
        $filter_email = $request->query('filter_email');
        $filter_state = $request->query('filter_state');
        $filter_status = $request->query('filter_status');
        $filter_is_owner = $request->query('filter_is_owner');
        $filter_q = $request->query('filter_q');
        $sort = $request->query('sort', 'created_at');
        $order = $request->query('order', 'desc');

        $filter_data = [
            'filter_name'     => $filter_name,
            'filter_username' => $filter_username,
            'filter_email'    => $filter_email,
            'filter_state'    => $filter_state,
            'filter_status'   => $filter_status,
            'filter_is_owner' => $filter_is_owner,
            'filter_q'        => $filter_q,
            'select'          => $fields->select,
            'sort'            => $sort,
            'order'           => $order,

            ...pageLimit($request),
        ];

        $results = CUser::getUsers($filter_data);

        $items = $meta_data = [];
        foreach ($results as $result) {
            $user_struct = new CUserStruct($result->getAttributes());
            $items[] = $user_struct->toArray([
                Struct::OPT_CHANGE => [
                    'created_at' => ['createdAtFormatted', 'iso'],
                    'image'      => ['getImage'],
                ],
                Struct::OPT_FILTER => $fields->select,
            ]);
        }

        if ($items) {
            $meta_data = ResMetaJson($results);
        }

        $json = [
            'items' => $items,
            '_meta' => $meta_data
        ];

        return resJson($json);
    }

    public function deleteUser(Request $request,string $id) :JsonResponse{
        $user = $this->CurrentAUser($request);
        $this_session = self::CurrentAdminAccessToken($user)->getAttribute('id');
        $delete_user = CUser::find($id);
        if (!$delete_user) {
            return resJson([
                'error' => [
                    'warning' => trans('v1/default.error_account_not_exist'),
                ],
                'code'  => 200,
            ]);
        } else {
            if ($user->getAttribute('id') == $id) {
                return resJson([
                    'error' => [
                        'warning' => trans('v1/auth.error_delete_my_account'),
                    ],
                    'code'  => 200,
                ]);
            }

            if (!$delete_user->getAttribute('status')) {
                return resJson([
                    'error' => [
                        'warning' => trans('v1/auth.error_username_status'),
                    ],
                    'code'  => 200,
                ]);
            }
        }

        AdminDeleteUserEvent::dispatch(request()->id, $this_session);

        CUser::setStatus($delete_user->getAttribute('id'), false);
        $json = [
            'code' => 200
        ];

        return resJson($json);
    }
}
