<?php

namespace App\Http\Controllers\V1\Account;


use App\Http\Controllers\V1\BaseController;
use App\Jobs\sendEmailVerifyLinkJob;
use App\Models\V1\Admin\AUser;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Events\Admin\AUserEmailVerificationEvent;


class EmailVerificationController extends BaseController
{
    public function checkVerification(EmailVerificationRequest $request): JsonResponse
    {
        $request->fulfill();
        /**@var $user AUser */
        $user     = $request->user();
        $token_id = explode('|', explode(' ', $request->header('Authorization'))[1])[0];
        AUserEmailVerificationEvent::dispatch($user->getAttribute('id'), $token_id);

        return resJson();
    }

    public function sendVerificationEmail(Request $request): JsonResponse
    {
        /**@var $user AUser */
        $user = AUser::find($request->input('id'));

        if (!$user) {

            return $this->invalidAuthRes();
        } else if ($user->hasVerifiedEmail()) {
            $json = [
                'code'  => 400,
                'error' => [
                    'email' => trans('v1/account.error_email_verified')
                ],
            ];
        } else {
            $token = $user->createToken($request->header('platform', 'unknown'), ['*'], $expiresAt ?? null);
            sendEmailVerifyLinkJob::dispatch($user)
                ->onConnection('database')->onQueue('thanhnt');

            $json = [
                'message' => trans('v1/account.resend_verify_notification', ['email' => $user->getAttribute('email')]),
                'token'   => $token->plainTextToken,
            ];
        }

        return resJson($json);
    }
}
