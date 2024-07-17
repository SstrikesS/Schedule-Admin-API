<?php

namespace App\Events\Admin;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AUserDeleteEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $activity = 'admin.user.delete';
    public function __construct(
        public string $id,
        public string $id_session,
        public ?array $data = null
    )
    {
        //
    }

}
