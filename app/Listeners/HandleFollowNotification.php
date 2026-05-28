<?php
namespace App\Listeners;

use App\Events\UserFollowed;
use App\Models\Notification;

class HandleFollowNotification
{
    public function handle(UserFollowed $event)
    {
        $follower = $event->follower;
        $targetUser = $event->targetUser;

        if ($event->isFollowing) {
            // THÊM FOLLOW -> BẮN THÔNG BÁO
            Notification::create([
                'user_id' => $targetUser->id, // Người nhận chuông
                'actor_id' => $follower->id, // Người gây ra hành động
                'type' => 'follow',
                'reference_id' => $follower->id, // Để bấm vô nhảy qua profile thằng follower
                'is_read' => 0
            ]);
        } else {
            // HỦY FOLLOW -> THU HỒI THÔNG BÁO
            Notification::where('user_id', $targetUser->id)
                ->where('actor_id', $follower->id)
                ->where('type', 'follow')
                ->delete();
        }
    }
}