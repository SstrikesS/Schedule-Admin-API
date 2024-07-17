<?php

namespace App\Http\Controllers\V1\Admin;

use App\Consts\DateFormat;
use App\Consts\Schema\DBAdminFields;
use App\Events\Admin\AUserDeleteEvent;
use App\Events\Admin\AUserRegisterEvent;
use App\Events\Admin\AUserResetPasswordEvent;
use App\Http\Controllers\V1\BaseController;
use App\Libs\QueryFields;
use App\Models\V1\Admin\ActivityModel;
use App\Models\V1\Admin\AUser;
use App\Structs\Struct;
use App\Structs\V1\Admin\AUserStruct;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminsController extends BaseController
{
    public function doAdd(Request $request): JsonResponse
    {
        $rule = [
            'name'     => 'between:3,100|required',
            'username' => 'between:3,50|required',
            'email'    => 'between:3,50|email:rfc,dns|required',
            'password' => 'between:4,100|required',
            'confirm'  => 'required|same:password',
            'image'    => 'image'
        ];

        $message = [
            'name.required'     => trans('v1/default.error_name_required'),
            'name.between'      => trans('v1/default.error_name_between', [
                'min' => 3,
                'max' => 100
            ]),
            'username.required' => trans('v1/default.error_username_required'),
            'username.between'  => trans('v1/default.error_username_between', [
                'min' => 3,
                'max' => 50
            ]),
            'email.required'    => trans('v1/default.error_email_required'),
            'email.between'     => trans('v1/default.error_email_between', [
                'min' => 3,
                'max' => 50
            ]),
            'email.email'       => trans('v1/default.error_email_email'),
            'password.required' => trans('v1/default.error_password_required'),
            'password.between'  => trans('v1/default.error_password_between', [
                'min' => 4,
                'max' => 100
            ]),
            'confirm.same'      => trans('v1/default.error_confirm_same_password'),
            'image'             => trans('v1/default.error_image_format'),
        ];

        $validator = $this->_validateForm($request, $rule, $message);


        if ($validator->errors()->count()) {
            $json = [
                'error' => firstError($validator->getMessageBag()->toArray())
            ];
        } else if (AUser::checkExists($request->only('username'))) {
            $json = [
                'code'  => 200, //400,
                'error' => [
                    'username' => trans('v1/auth.error_username_existed'),
                ]
            ];
        } else if (AUser::checkExists($request->only('email'))) {
            $json = [
                'code'  => 200, //400,
                'error' => [
                    'email' => trans('v1/auth.error_username_existed'),
                ]
            ];
        } else {
            $status = $request->input('status', true);
            if (!empty($request->file('image'))) {
                $avatar = $request->file('image')->store('profile');
            }

            $insert_data = [
                'id'         => generateUlid(),
                'parent_id'  => $request->input('parent_id'),
                'name'       => $request->input('name'),
                'username'   => $request->input('username'),
                'email'      => $request->input('email'),
                'address'    => $request->input('address'),
                'phone'      => $request->input('phone'),
                'password'   => Hash::make($request->input('password')),
                'status'     => $status,
                'image'      => $avatar ?? 'profile/no_avatar.jpg',
                'created_at' => now()->format(DateFormat::TIMESTAMP_DB),
            ];

            $insert_data = normalizeToSQLViaArray($insert_data, DBAdminFields::USERS);
            if ($insert_data && $id = AUser::addUserGetID($insert_data)) {
                $user_struct = new AUserStruct($insert_data);

                $json = [
                    'data' => [
                        ...$user_struct->toArray([
                            Struct::OPT_CHANGE => [
                                'user_agent' => ['handleDeviceAction'],
                            ],
                            Struct::OPT_IGNORE => [
                                'password'
                            ]
                        ])
                    ]
                ];
                AUserRegisterEvent::dispatch(request()->user()->id, $id, request()->user()->currentAccessToken()->id, ['old' => Hash::make($request->input('password'))]);
            } else {
                $json = [
                    'code'  => 200, //400,
                    'error' => [
                        'warning' => trans('v1/default.error_insert'),
                    ]
                ];
            }
        }

        return resJson($json);
    }

    public function getUser(Request $request, string $id): JsonResponse
    {
        $fields = new QueryFields($request, DBAdminFields::USERS);

        if (!$user_info = AUser::getUser($id, $fields->select)) {
            return resJson([
                'error' => [
                    'warning' => trans('v1/default.error_id_exists')
                ]
            ]);
        }

        $user_struct = new AUserStruct($user_info->getAttributes());

        return resJson([
            'data' => $user_struct->toArray([
                Struct::OPT_CHANGE => [
                    'created_at' => ['createdAtFormatted', 'iso'],
                    'image'      => ['getImage'],
                ],
                Struct::OPT_FILTER => $fields->select,
            ])
        ]);
    }

    public function getUsers(Request $request): JsonResponse
    {
        $fields = new QueryFields($request, DBAdminFields::USERS);

        $filter_name     = $request->query('filter_name');
        $filter_username = $request->query('filter_username');
        $filter_email    = $request->query('filter_email');
        $filter_state    = $request->query('filter_state');
        $filter_status   = $request->query('filter_status');
        $filter_is_owner = $request->query('filter_is_owner');
        $filter_q        = $request->query('filter_q');
        $sort            = $request->query('sort', 'created_at');
        $order           = $request->query('order', 'desc');

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

        $results = AUser::getUsers($filter_data);

        $items = $meta_data = [];

        foreach ($results as $result) {
            $user_struct = new AUserStruct($result->getAttributes());
            $items[]     = $user_struct->toArray([
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

    public function deleteUser(Request $request, string $id): JsonResponse
    {
        $user         = $this->CurrentAUser($request);
        $this_session = self::CurrentAdminAccessToken($user)->getAttribute('id');
        $delete_user  = AUser::find($id);

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

        AUserDeleteEvent::dispatch(request()->id, $this_session);

        AUser::setStatus($delete_user->getAttribute('id'), false);
        $json = [
            'code' => 200
        ];

        return resJson($json);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $rule      = [
            'password' => 'required|between:6,30|confirmed',
        ];
        $message   = [
            'password.required'  => trans('v1/auth.error_password_required'),
            'password.min'       => trans('v1/auth.error_password_between', [
                'min' => 6,
                'max' => 30,
            ]),
            'password.confirmed' => trans('v1/account.error_password_confirmed'),
        ];
        $validator = $this->_validateForm($request, $rule, $message);

        $old_password = ActivityModel::getOldPassword($this->user);
        foreach ($old_password as $value) {
            if ($value->data == null) {
                continue;
            } elseif (Hash::check($request->input('password'), json_decode($value->data)->old)) {
                $value->created_at = Carbon::parse($value->created_at)->format(DateFormat::TIMESTAMP_DB);

                $validator->errors()->add('password', trans('v1/account.duplicate_reset_old_password', ['time' => $value->created_at]));
                break;
            }
        }

        if ($validator->errors()->count()) {

            $json = [
                'code'  => 200, //400
                'error' => firstError($validator->getMessageBag()->toArray())
            ];
        } else {
            $this->user->forceFill(
                [
                    'password' => Hash::make($request->input('password'))
                ]
            );
            $this->user->save();

            AUserResetPasswordEvent::dispatch($this->user->getAttribute('id'), 'Guest', ['old' => $this->user->getAttribute('password'), 'new' => Hash::make($request->input('password'))]);

            $json = [
                'code' => 200,
            ];
        }

        return resJson($json);
    }

    protected function _validateForm(Request $request, ?array $rule = [], ?array $message = []): \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
    {
        $validator = Validator::make($request->all(), $rule, $message);

        if (!$validator->fails()) {
            $this->CurrentAUser($request);
            if ($this->user instanceof AUser) {
                if (!$this->user->getAttribute('status')) {
                    $validator->errors()->add('username', trans('v1/auth.error_username_status'));
                }
            } else {
                $validator->errors()->add('user', trans('v1/auth.error_warning'));
            }
        }

        return $validator;
    }
}
