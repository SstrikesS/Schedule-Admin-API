<?php


namespace App\Events\User;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminEditUserEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $activity = 'admin.edit.user';

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string  $id,
        public array   $data,
        public ?string $id_session,
    )
    {
        //
    }
}
