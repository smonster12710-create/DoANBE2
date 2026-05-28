<?php
namespace App\Listeners;

use App\Events\PostMention;
use App\Models\Notification;

class HandleMentionNotification
{
    public function handle(PostMention $event)
    {
        $actor = $event->mentioner;
        $post = $event->post;
        $mentionedUser = $event->mentionedUser;

        //  Tự mention bài mình thì âm thầm bỏ qua, khỏi báo
        if ($actor->id == $mentionedUser->id) {
            return;
        }

        // Bắn thông báo
        Notification::create([
            'user_id' => $mentionedUser->id, // Người bị mention
            'actor_id' => $actor->id, // Thằng đi mention
            'type' => 'mention', // Chuẩn type của DB nha
            'reference_id' => $post->id, // Lưu ID bài post
            'is_read' => 0
        ]);
    }
}