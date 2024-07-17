<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Sanctum\PersonalAccessToken;
use App\Models\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        /** @var $auth AuthManager */

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return "https://dev-api-amc.lichtrinh.net/reset-password/$token/" . $user->getAttribute('email');
        });

        VerifyEmail::createUrlUsing(function ($notifiable) {
            $frontendUrl = 'https://dev-api-amc.lichtrinh.net/';

            $verifyUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id'   => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            return $frontendUrl . explode('v1/account/', ($verifyUrl))[1];
        });

        $auth = $this->app['auth'];

        $auth->viaRequest('api', function (Request $request) {

            if ($request->bearerToken()) {
                $token = PersonalAccessToken::findToken($request->bearerToken());

                if ($token) {
                    $info = User::query()
                        ->where([
                            ['id', $token->getAttribute('tokenable_id')],
                            ['status', true]
                        ])
                        ->distinct()->first();

                    if ($info) {
                        $info->withAccessToken($token);

                        return $info;
                    }
                }
            }

            return null;
        });
    }
}
