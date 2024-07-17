<?php

namespace App\Http\Controllers\V1\Account;

use App\Consts\Schema\DBAdminFields;
use App\Events\Admin\AUserEditEvent;
use App\Http\Controllers\V1\BaseController;
use App\Libs\NorIntoDB;
use App\Models\V1\Admin\AUser;
use App\Structs\Struct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MeController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        if ($this->CurrentAUser($request)) {
            $user_struct = $this->user->struct();
        } else {

            return resJson([
                'code'  => 200, //400,
                'error' => [
                    'user' => trans('v1/auth.error_username_not_exist'),
                ],
            ]);
        }

        return resJson([
            'data' => $user_struct->toArray([
                Struct::OPT_CHANGE => [
                    'image' => ['getImage']  // process image by function inside struct
                ],
                Struct::OPT_IGNORE => [
                    'status'
                ]
            ])
        ]);
    }

    public function doEditMe(Request $request): JsonResponse
    {
        $id         = $request->user()->id;
        $id_session = $request->user()->currentAccessToken()->id;

        $validator = $this->_validateForm($request, [], []);

        if ($request->input('username')) {
            $validator->errors()->add('account', trans('v1/account.error_edit_username'));
        }
        if ($request->input('email')) {
            $validator->errors()->add('account', trans('v1/account.error_edit_email'));
        }
        if ($request->input('password')) {
            $validator->errors()->add('account', trans('v1/account.error_edit_password'));
        }

        if ($validator->errors()->count()) {
            $json = [
                'error' => firstError($validator->getMessageBag()->toArray())
            ];
        } else {

            $post_data = normalizeToRequest($request->all(), DBAdminFields::USERS);
            $nor_into  = new NorIntoDB();

            if (!empty($request->file('image'))) {
                $post_data['image'] = $request->file('image')->store('profile');
            }
            if ($nor_into->viaDB($post_data, $request->user()->getAttributes())) {
                if ($image_link = $this->CurrentAUser($request)->getAttribute('image')) {
                    if (Storage::exists($image_link)) {
                        Storage::delete($image_link);
                    }
                }
                AUser::editUser($id, $nor_into);
                AUserEditEvent::dispatch($post_data, $id_session);

                $json = [
                    'data' => $post_data
                ];
            } else {
                $json = [
                    'error' => [
                        'warning' => trans('v1/default.error_insert')
                    ],
                    'code'  => 200, //400,
                ];
            }
        }

        return resJson($json);
    }

    protected function _validateForm(Request $request, ?array $rule = [], ?array $message = []): \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
    {
        $validator = Validator::make($request->all(), $rule, $message);

        if (!$validator->fails()) {
            // Check user
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
