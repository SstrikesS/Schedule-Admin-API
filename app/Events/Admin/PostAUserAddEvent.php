<?php

namespace App\Events\Admin;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostAUserAddEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $activity = 'admin.user.add';

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $id,
        public ?array  $data,
    )
    {
        //
    }
}
