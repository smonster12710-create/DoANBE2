<?php

namespace App\Observers;

use App\Models\Post;

class PostObserver
{
    /**
     * Handle the Post "creating" event.
     */
    public function creating(Post $post): void
    {
        // Ưu tiên 1: Kiểm tra checkbox từ form (user có thể override cài đặt toàn bộ)
        if (request()->has('is_anonymous')) {
            $post->is_anonymous = 1;
        } elseif (auth()->check() && auth()->user()->anonymous_posts) {
            // Ưu tiên 2: Nếu không có checkbox, kiểm tra cài đặt ẩn danh toàn bộ
            $post->is_anonymous = 1;
        } else {
            // Nếu không có checkbox và cài đặt không bật, post công khai
            $post->is_anonymous = 0;
        }
    }


}