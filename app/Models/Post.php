<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    // Một bài viết có nhiều lượt Like
    public function likes()
    {
        return $this->hasMany(Like::class, 'post_id');
    }

    // Lấy danh sách những người dùng đã like bài này
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'likes', 'post_id', 'user_id');
    }

    // Quan hệ với người đăng bài viết
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
