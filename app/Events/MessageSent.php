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

    public function broadcastOn()
    {
        $channels = [];

        // 1. Kênh phòng chat (Dành cho khung chat chi tiết chat.js nghe)
        $channels[] = new PrivateChannel('chat-conversation.' . $this->message->conversation_id);

        // 2. Kênh cá nhân (Dành cho Sidebar list_chat.js nghe)
        if (!empty($this->receiverIds)) {
            foreach ($this->receiverIds as $id) {
                $channels[] = new PrivateChannel('user.' . $id);
            }
        } else {
            // Dự phòng nếu không truyền mảng ID thì phát về kênh của người gửi
            $channels[] = new PrivateChannel('user.' . $this->message->sender_id);
        }

        return $channels;
    }
    public function broadcastAs()
    {
        return 'MessageSent';
    }
}
