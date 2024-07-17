<?php


namespace App\Events\User;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminAddUserEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $activity = 'admin.add.user';

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
