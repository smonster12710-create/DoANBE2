<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['user_id', 'content', 'image_url', 'video_url'];

    // Mối quan hệ với User (Một bài viết thuộc về một người dùng)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Mối quan hệ Many-to-Many với Hashtag qua bảng trung gian post_hashtags
    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'post_hashtags');
    }
}
