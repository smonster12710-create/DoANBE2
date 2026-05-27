<?php

namespace App\Listeners;

use App\Events\PostComment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleCommentNotification
{

    /**
     * Handle the event.
     */
    public function handle(PostComment $event)
    {
        $actor = $event->commenter;
        $post = $event->post;
        $comment = $event->comment;

        //  Tự comment bài mình thì âm thầm bỏ qua, khỏi báo
        if ($actor->id == $post->user_id) {
            return;
        }

        // Bắn thông báo
        \App\Models\Notification::create([
            'user_id' => $post->user_id, // Chủ bài viết
            'actor_id' => $actor->id, // Thằng đi comment
            'type' => 'comment', // Chuẩn type của DB nha
            'reference_id' => $post->id, // Lưu ID bài post
            'is_read' => 0
        ]);
    }
}
