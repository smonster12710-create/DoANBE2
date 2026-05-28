<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostLiked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userLiking; // Đại ca đi thả tim
    public $post; // Bài viết được thả tim
    public $isLiked; // true là Like, false là Unlike

    public function __construct($userLiking, $post, $isLiked)
    {
        $this->userLiking = $userLiking;
        $this->post = $post;
        $this->isLiked = $isLiked;
    }
}