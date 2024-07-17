<?php

namespace App\Http\Controllers\V1\Auth;

use App\Events\Admin\AUserLoginEvent;
use App\Http\Controllers\Controller;
use App\Models\V1\Admin\AUser;
use App\Structs\Struct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    protected AUser $user_info;

    public function index(Request $request): JsonResponse
    {
        $rule = [
            'username' => 'required|between:3,30',
            'password' => 'required|between:6,30'
        ];

        $message = [
            'username.required' => trans('v1/auth.error_username_required'),
            'username.between'  => trans('v1/auth.error_username_between', [
                'min' => 3,
                'max' => 30
            ]),
            'password.required' => trans('v1/auth.error_password_required'),
            'password.min'      => trans('v1/auth.error_password_between', [
                'min' => 6,
                'max' => 30
            ])
        ];

        $validator = $this->_validate($request, $rule, $message);

        if ($validator->errors()->count()) {
            $json = [
                'error' => firstError($validator->getMessageBag()->toArray())
            ];
        } else {
            $user_struct = $this->user_info->struct();

            if (config('sanctum.expiration')) {
                $expiresAt = now()->addMinutes(config('sanctum.expiration'));
            }

            $token = $this->user_info->newCreateToken($request->header('platform', 'unknown'), ['*'], $expiresAt ?? null);

            $json = [
                'data' => [
                    ...$user_struct->toArray([
                        Struct::OPT_CHANGE => [
                            'image' => ['getImage']  // process image by function inside struct
                        ],
                        Struct::OPT_IGNORE => [
                            'status',
                            'password'
                        ]
                    ]),
                    'access_token' => [
                        'token'      => $token->plainTextToken,
                        'abilities'  => $token->accessToken->getAttribute('abilities'),
                        'expires_at' => $token->accessToken->getAttribute('expires_at'),
                        'updated_at' => $token->accessToken->getAttribute('updated_at'),
                        'created_at' => $token->accessToken->getAttribute('created_at'),
                    ],
                ],
            ];
            AUserLoginEvent::dispatch($user_struct->id, $token->accessToken->getAttribute('id'));
        }

        return resJson($json);
    }

    protected function _validate(Request $request, $rule, $message): \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
    {
        $validator = Validator::make($request->all(), $rule, $message);
        if (!$validator->fails()) {
            $username = $request->input('username');

            $user_info = AUser::getUserByName($username, ['*']);

            if (!$user_info) {
                $validator->errors()->add('username', trans('v1/auth.error_username_not_exist'));
            } else {
                if (!$user_info->getAttribute('status')) {
                    $validator->errors()->add('username', trans('v1/auth.error_username_status'));
                } elseif (!Hash::check($request->input('password'), $user_info->getAttribute('password'))) {
                    $validator->errors()->add('password', trans('v1/auth.error_password_incorrect'));
                }
                if ($user_info instanceof AUser) {
                    $this->user_info = $user_info;
                }
            }
        }

        return $validator;
    }
}
