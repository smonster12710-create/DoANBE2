<?
namespace App\Listeners;

use App\Events\FriendRequestSent;
use App\Models\Notification;

class HandleFriendRequestNotification
{
    public function handle(FriendRequestSent $event)
    {
        $sender = $event->sender;
        $receiver = $event->receiver;

        Notification::create([
            'user_id' => $receiver->id, // Người nhận lời mời kết bạn
            'actor_id' => $sender->id, // Người gửi
            'type' => 'friend_request',
            'reference_id' => $sender->id, // Để bấm vô nhảy qua profile người gửi
            'is_read' => 0
        ]);
    }
}