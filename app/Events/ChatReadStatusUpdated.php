<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ChatReadStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversationId;
    public $updatedMessages;
    public $senderIds;

    public function __construct($conversationId, $updatedMessages, $senderIds = [])
    {
        $this->conversationId = $conversationId;
        $this->updatedMessages = $updatedMessages;
        $this->senderIds = $senderIds;
    }

    // SỬA TẠI ĐÂY: Thêm ép kiểu array và bọc ngoặc vuông [] để trả về một mảng các channel
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat-conversation.' . $this->conversationId)
        ];
    }

    public function broadcastAs()
    {
        return 'ChatReadStatusUpdated';
    }
}
