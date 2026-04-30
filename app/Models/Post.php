<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\PostMedia;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'image_url',
        'video_url'
    ];

    protected $with = ['user'];

    // --- CÁC MỐI QUAN HỆ (RELATIONS) ---

    /**
     * Quan hệ với người đăng bài viết
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Một bài viết có nhiều lượt Like
     */
    public function likes()
    {
        return $this->hasMany(Like::class, 'post_id');
    }

    /**
     * Lấy danh sách những người dùng đã like bài này (Many-to-Many)
     */
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'likes', 'post_id', 'user_id');
        // ->withTimestamps();
    }

    /**
     * Mối quan hệ với Hashtag qua bảng trung gian post_hashtags
     */
    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'post_hashtags')
            ->withTimestamps();
    }
    public function media()
    {
        // Giả sử bảng của bạn tên là post_media
        return $this->hasMany(PostMedia::class, 'post_id');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
