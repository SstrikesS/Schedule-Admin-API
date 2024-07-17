<?php

namespace App\Events\Admin;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AUserRegisterEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $activity = 'admin.user.register';

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $user_id,
        public string $id,
        public string $id_session,
        public ?array $data = null
    )
    {
        //
    }


}
