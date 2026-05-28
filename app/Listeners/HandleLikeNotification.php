<?php
namespace App\Listeners;

use App\Events\PostLiked;
use App\Models\Notification;

class HandleLikeNotification
{
    public function handle(PostLiked $event)
    {
        $actor = $event->userLiking;
        $post = $event->post;

        // Chốt chặn tấu hài: Tự like bài mình thì âm thầm bỏ qua, khỏi báo
        if ($actor->id == $post->user_id) {
            return;
        }

        if ($event->isLiked) {
            // NHÁNH 1: ĐANG LIKE -> Bắn thông báo
            Notification::create([
                'user_id' => $post->user_id, // Chủ bài viết
                'actor_id' => $actor->id, // Thằng đi Like
                'type' => 'like_post', // Chuẩn type của DB nha
                'reference_id' => $post->id, // Lưu ID bài post
                'is_read' => 0
            ]);
        } else {
            // NHÁNH 2: UNLIKE -> Dọn cái thông báo cũ đi
            Notification::where('user_id', $post->user_id)
                ->where('actor_id', $actor->id)
                ->where('type', 'like_post')
                ->where('reference_id', $post->id)
                ->delete();
        }
    }
}