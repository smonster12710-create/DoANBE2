<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserActivityUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $activity;
    public array $receiverIds;

    public function __construct(array $activity, array $receiverIds)
    {
        $this->activity = $activity;
        $this->receiverIds = array_values(array_unique(array_map('intval', $receiverIds)));
    }

    public function broadcastOn(): array
    {
        return array_map(
            fn ($id) => new PrivateChannel('user.' . $id),
            $this->receiverIds
        );
    }

    public function broadcastAs(): string
    {
        return 'UserActivityUpdated';
    }
}
