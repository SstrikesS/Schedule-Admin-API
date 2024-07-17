<?php

namespace App\Http\Controllers\V1\Account;

use App\Consts\DateFormat;
use App\Events\Admin\AUserResetPasswordEvent;
use App\Http\Controllers\Controller;
use App\Jobs\OTPEmailResetPasswordJob;
use App\Jobs\sendResetPasswordEmailJob;
use App\Models\V1\Admin\ActivityModel;
use App\Models\V1\Admin\AUser;
use App\Models\V1\Admin\OTPModel;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    protected ?AUser $user;
    protected string $key = 'admin.password.reset';

    public function sendLinkByEmail(Request $request): JsonResponse
    {
        $rule = [
            'username' => 'required|between:3,30'
        ];

        $message = [
            'username.required' => trans('v1/auth.error_username_required'),
            'username.between'  => trans('v1/auth.error_username_between', [
                'min' => 3,
                'max' => 30
            ]),
        ];

        $validator = $this->_validate($request, $rule, $message);

        if ($validator->errors()->count()) {
            $json = [
                'code'  => 200, //400,
                'error' => firstError($validator->getMessageBag()->toArray())
            ];
        } else {
            $json = [
                'message' => trans('v1/account.reset_password_link_sent', ['email' => $this->user->getAttribute('email')]),
                'email'   => $this->user->getAttribute('email'),
            ];

            sendResetPasswordEmailJob::dispatch($this->user)
                ->onConnection('database');
        }

        return resJson($json);
    }

    public function checkLink(Request $request): JsonResponse
    {
        $rule = [
            'token' => 'required',
            'email' => 'required|between:3,50|email:rfc,dns',
        ];

        $message = [
            'email.required' => trans('v1/default.error_email_required'),
            'email.between'  => trans('v1/default.error_email_between', [
                'min' => 3,
                'max' => 50
            ]),
            'email.email'    => trans('v1/default.error_email_email'),
            'token.required' => trans('v1/default.error_token_required')
        ];

        $validator = $this->_validate($request, $rule, $message);

        if ($validator->errors()->count()) {
            $json = [
                'code'  => 200, //400,
                'error' => firstError($validator->getMessageBag()->toArray())
            ];
        } else if (!Password::tokenExists($this->user, $request->input('token'))) {
            $json = [
                'code'  => 200, //400,
                'error' => [
                    'token' => trans('v1/account.error_token_password_reset'),
                ]
            ];
        } else {
            $json = [
                'code' => 200,
            ];
        }

        return resJson($json);
    }

    public function resetPasswordViaLink(Request $request): JsonResponse
    {
        $rule = [
            'password' => 'required|between:6,30|confirmed',
            'email'    => 'required|between:3,50|email:rfc,dns',
            'token'    => 'required'
        ];

        $message = [
            'password.required'  => trans('v1/auth.error_password_required'),
            'password.min'       => trans('v1/auth.error_password_between', [
                'min' => 6,
                'max' => 30,
            ]),
            'password.confirmed' => trans('v1/account.error_password_confirmed'),
            'email.required'     => trans('v1/default.error_email_required'),
            'email.between'      => trans('v1/default.error_email_between', [
                'min' => 3,
                'max' => 50
            ]),
            'email.email'        => trans('v1/default.error_email_email'),
            'token.required'     => trans('v1/default.error_token_required')
        ];

        $validator = $this->_validate($request, $rule, $message);

        if ($this->user) {
            if (!Password::tokenExists($this->user, $request->input('token'))) {
                $validator->errors()->add('token', trans('v1/account.error_token_password_reset'));
            }

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
        }

        if ($validator->errors()->count()) {
            $json = [
                'code'  => 200, //400,
                'error' => firstError($validator->getMessageBag()->toArray())
            ];
        } else {
            Password::reset($request->only('email', 'password', 'password_confirmation', 'token'),
                function (AUser $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ]);
                    $user->save();
                    event(new PasswordReset($user));
                }
            );

            AUserResetPasswordEvent::dispatch($this->user->getAttribute('id'), 'Guest', ['old' => $this->user->getAttribute('password'), 'new' => Hash::make($request->input('password'))]);

            $json = [
                'code' => 200,
            ];
        }

        return resJson($json);
    }

    public function sendOTP(Request $request): JsonResponse
    {
        $rule = [
            'username' => 'required|between:3,30'
        ];

        $message = [
            'username.required' => trans('v1/auth.error_username_required'),
            'username.between'  => trans('v1/auth.error_username_between', [
                'min' => 3,
                'max' => 30
            ]),
        ];

        $validator = $this->_validate($request, $rule, $message);

        if ($validator->errors()->count()) {
            $json = [
                'code'  => 200, //400,
                'error' => firstError($validator->getMessageBag()->toArray())
            ];
        } else {
            $otp = OTPModel::generateOTP($this->user, $this->key);

            OTPEmailResetPasswordJob::dispatch($this->user, $otp)
                ->onConnection('database');

            $json = [
                'code'    => 200,
                'message' => trans('v1/account.send_otp_message', ['email' => $this->user->getAttribute('email')]),
                'email'   => $this->user->getAttribute('email'),
            ];
        }

        return resJson($json);
    }

    public function resetPasswordViaOTP(Request $request): JsonResponse
    {
        $rule    = [
            'email'    => 'between:3,50|email:rfc,dns',
            'password' => 'required|between:6,30|confirmed',
            'otp'      => 'required|digits:6'
        ];
        $message = [
            'email.required'     => trans('v1/default.error_email_required'),
            'email.between'      => trans('v1/default.error_email_between', [
                'min' => 3,
                'max' => 50
            ]),
            'email.email'        => trans('v1/default.error_email_email'),
            'password.required'  => trans('v1/auth.error_password_required'),
            'password.min'       => trans('v1/auth.error_password_between', [
                'min' => 6,
                'max' => 30,
            ]),
            'password.confirmed' => trans('v1/account.error_password_confirmed'),
            'otp.required'       => trans('v1/account.error_otp_required'),
            'otp.digits'         => trans('v1/account.error_otp_type'),
            'otp.value'          => trans('v1/account.error_otp_type'),
        ];

        $validator = $this->_validate($request, $rule, $message);

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

        if (!OTPModel::checkOTP($this->user->getAttribute('id'), $request->input('otp'), $this->key)) {

            $validator->errors()->add('otp', trans('v1/account.error_2fa_code'));
        }

        if ($validator->errors()->count()) {
            $json = [
                'code'  => 200, //400,
                'error' => firstError($validator->getMessageBag()->toArray())
            ];
        } else {
            $this->user->forceFill([
                'password' => Hash::make($request->input('password'))
            ]);
            $this->user->save();
            AUserResetPasswordEvent::dispatch($this->user->getAttribute('id'), ['old' => $this->user->getAttribute('password'), 'new' => Hash::make($request->input('password'))]);

            $json = [
                'code' => 200,
            ];
        }

        return resJson($json);
    }

    protected function _validate(Request $request, ?array $rule = [], ?array $message = []): \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator
    {
        $validator  = Validator::make($request->all(), $rule, $message);
        $this->user = null;
        if (!$validator->fails()) {
            $username = $request->input('username') ? $request->input('username') : $request->input('email') ?? null;

            if ($username) {
                /**@var $user AUser|null */
                $user = AUser::getUserByName($username, ['*']);

                if ($user instanceof AUser) {
                    if (!$user->getAttribute('status')) {
                        $validator->errors()->add('username', trans('v1/auth.error_username_status'));
                    } else if (!$user->getAttribute('email_verified_at')) {
                        $validator->errors()->add('email', trans('v1/account.error_email_unverified'));
                    } else {
                        $this->user = $user;
                    }
                } else {
                    $validator->errors()->add('username|email', trans('v1/auth.error_username_not_exist'));
                }
            } else {
                $validator->errors()->add('username|email', trans('v1/auth.error_username_not_exist'));
            }
        }

        return $validator;
    }
}
