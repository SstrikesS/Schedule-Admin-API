<?php

namespace App\Listeners;


use App\Events\Admin\AUserDeleteEvent;
use App\Events\Admin\AUserEmailVerificationEvent;
use App\Events\Admin\AUserLoginEvent;
use App\Events\Admin\AUserLogoutEvent;
use App\Events\Admin\AUserRegisterEvent;
use App\Events\Admin\AUserResetPasswordEvent;
use App\Events\Admin\AUserEditEvent;
use App\Events\User\AdminAddUserEvent;
use App\Events\User\AdminDeleteUserEvent;
use App\Events\User\AdminEditUserEvent;
use App\Models\V1\Admin\ActivityModel;
use App\Models\V1\Admin\AUser;
use Illuminate\Support\Str;

class AUserActivityEventSubscriber
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if (isset($event->activity)) {
            $insert_data = [
                'key' => $event->activity,
            ];

            if (!empty($event->data) && is_array($event->data)) {
                $_data = rmArrayObjectByValue($event->data);

                if (isset($_data['created_at'])) {
                    unset($_data['created_at']);
                }

                if (isset($_data['updated_at'])) {
                    unset($_data['updated_at']);
                }

                $insert_data['data'] = formatJsonToSQL($_data);
            }

            if (!empty($event->user_id) && Str::isUlid($event->user_id)) {
                $insert_data['user_id'] = $event->user_id;
            }

            if (!empty($event->id)) {
                $insert_data['ref_id'] = $event->id;
            }

            if (!empty($event->id_session)) {
                $insert_data['id_session'] = $event->id_session;
            }

            ActivityModel::addUserActivity($insert_data);
        }
    }

    public function handleOptionLogout(object $event): void
    {
        if (isset($event->activity)) {
            $insert_data = [
                'key' => $event->activity,
            ];

            if (!empty($event->data) && is_array($event->data)) {
                $_data = rmArrayObjectByValue($event->data);

                if (isset($_data['created_at'])) {
                    unset($_data['created_at']);
                }

                if (isset($_data['updated_at'])) {
                    unset($_data['updated_at']);
                }

                $insert_data['data'] = formatJsonToSQL($_data);
            }

            if (!empty($event->user_id) && Str::isUlid($event->user_id)) {
                $insert_data['user_id'] = $event->user_id;
            }

            if (!empty($event->id)) {
                $insert_data['ref_id'] = $event->id;
            }

            if (!empty($event->id_session)) {
                $insert_data['id_session'] = $event->id_session;
            }
            if (!empty($event->ip)) {
                $insert_data['ip'] = $event->ip;
            }
            if (!empty($event->user_agent)) {
                $insert_data['user_agent'] = $event->user_agent;
            }
            ActivityModel::addUserActivity($insert_data);
        }
    }

    public function subscribe(): array
    {
        return [
            // Auth Admin User
            AUserLoginEvent::class             => 'handle',
            AUserLogoutEvent::class            => 'handle',
            AUserEmailVerificationEvent::class => 'handle',
            AUserResetPasswordEvent::class     => 'handle',
            AUserEditEvent::class              => 'handle',
            AUserDeleteEvent::class            => 'handle',
            AUserRegisterEvent::class          => 'handle',
            AdminAddUserEvent::class           => 'handle',
            AdminEditUserEvent::class          => 'handle',
            AdminDeleteUserEvent::class        => 'handle',
        ];
    }
}
