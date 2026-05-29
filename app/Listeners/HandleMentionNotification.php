<?php

namespace App\Listeners;

use App\Events\PostMention;
use App\Models\Notification;
use App\Models\User; // NHỚ USE THÊM ÔNG NỘI NÀY VÔ NHA

class HandleMentionNotification
{
    public function handle(PostMention $event)
    {
        $actor = $event->mentioner;
        $post = $event->post;
        $usernames = $event->mentionedUser; // Mảng chứa ['user_1', 'user_2', ...]

        // 1. Dùng whereIn để bươi dưới Database lên user bị nhắc tên
        $taggedUsers = User::whereIn('username', $usernames)->get();

        // 2. Chạy vòng lặp để phát thông báo cho từng user
        foreach ($taggedUsers as $taggedUser) {

            // Chốt chặn: Tự réo tên mình thì lơ đi, khỏi báo
            if ($actor->id == $taggedUser->id) {
                continue;
            }

            // Bắn thông báo
            Notification::create([
                'user_id' => $taggedUser->id, // Người bị mention
                'actor_id' => $actor->id,      // Thằng đi mention
                'type' => 'mention',       // Chuẩn type mention
                'reference_id' => $post->id,       // Lưu ID bài post
                'is_read' => 0
            ]);
        }
    }
}