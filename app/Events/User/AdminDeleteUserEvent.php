<?php

namespace App\Events\User;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminDeleteUserEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $activity = 'admin.delete.user';
    public function __construct(
        public string $id,
        public string $id_session,
        public ?array $data = null
    )
    {
        //
    }

}
