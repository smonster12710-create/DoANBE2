<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $receiverIds;

    public function __construct(
        Message $message,
        $receiverIds = []
    ) {
        $this->message = $message->load('sender');
        $this->receiverIds = $receiverIds;
    }

    public function broadcastOn(): array
    {
        $channels = [

            // realtime trong phòng chat
            new PrivateChannel(
                'chat-conversation.' .
                    $this->message->conversation_id
            )
        ];

        // realtime badge theo user
        foreach ($this->receiverIds as $id) {

            $channels[] =
                new PrivateChannel(
                    'user.' . $id
                );
        }

        return $channels;
    }
    public function broadcastAs()
    {
        return 'MessageSent';
    }
}
