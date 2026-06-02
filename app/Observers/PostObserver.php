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
        // Chộp lấy cái request đang bay tới từ Form
        // Nếu user có tick vô checkbox "is_anonymous" thì mình gán cờ = 1 (true)
        if (request()->has('is_anonymous')) {
            $post->is_anonymous = 1;
        } else {
            $post->is_anonymous = 0;
        }
    }


}