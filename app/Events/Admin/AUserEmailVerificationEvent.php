<?php

namespace App\Events\Admin;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AUserEmailVerificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $activity = 'admin.user.email.verification';

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $user_id,
        public string $id_session,
        public ?array $data = null
    )
    {
        //
    }



}
