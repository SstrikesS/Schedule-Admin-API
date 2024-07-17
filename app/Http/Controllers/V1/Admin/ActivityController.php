<?php

namespace App\Http\Controllers\V1\Admin;

use App\Consts\Schema\DBActivityFields;
use App\Http\Controllers\V1\BaseController;
use App\Libs\QueryFields;
use App\Models\Sanctum\PersonalAccessToken;
use App\Models\V1\Admin\ActivityModel;
use App\Structs\Struct;
use App\Structs\V1\Admin\AUserActivityStruct;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ActivityController extends BaseController
{
    public function getActivities(Request $request): JsonResponse
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
            $items = [];
            $user  = $this->CurrentAUser($request);

            $fields_activity = new QueryFields($request, DBActivityFields::ACTIVITY);
            $filter_data = [
                'user_id'   => $user->getAttribute('id'),
                'select'    => $fields_activity->select,
                'sort_by'   => $request->input('sort_by') ?? 'created_at',
                'sort'      => $request->input('sort') ?? 'desc',
                'search_by' => $request->input('search_by') ?? null,
                'key'       => "%{$request->input('key')}%" ?? '%%',
                ...pageLimit($request),
            ];

            $activities      = ActivityModel::getUserActivities($request, $filter_data);

            foreach ($activities as $activity) {
                $activity_struct = new AUserActivityStruct($activity->getAttributes());
                $items[]         = $activity_struct->toArray([

                    Struct::OPT_CHANGE => [
                        'user_agent' => ['handleDeviceAction'],
                    ],

                    Struct::OPT_FILTER => $fields_activity->select,

                    Struct::OPT_IGNORE => [
                        'user_id', 'data'
                    ],
                    Struct::OPT_EXTRA  => [
                        'state' => PersonalAccessToken::find($activity_struct->id_session) != null ? PersonalAccessToken::find($activity_struct->id_session)->state : -1,
                    ],
                ]);
            }
            $json = [
                'items' => $items,
                '_meta' => ResMetaJson($activities)
            ];
        }
        return resJson($json);
    }
}
