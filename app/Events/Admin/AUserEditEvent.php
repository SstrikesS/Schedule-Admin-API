<?php

namespace App\Events\Admin;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AUserEditEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $activity = 'admin.user.edit';

    /**
     * Create a new event instance.
     */
    public function __construct(
        public array   $data,
        public ?string $id_session,
    )
    {
        //
    }
}
