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
            // Realtime trong phòng chat chi tiết
            new PrivateChannel('chat-conversation.' . $this->message->conversation_id)
        ];

        // 1. Realtime badge cho những người NHẬN tin nhắn
        foreach ($this->receiverIds as $id) {
            $channels[] = new PrivateChannel('user.' . $id);
        }

        // 2. THÊM DÒNG NÀY: Bắn ngược lại cho chính NGƯỜI GỬI để tự cập nhật Sidebar của mình
        if ($this->message->user_id) {
            $channels[] = new PrivateChannel('user.' . $this->message->user_id);
        }

        return $channels;
    }
    public function broadcastAs()
    {
        return 'MessageSent';
    }
}
