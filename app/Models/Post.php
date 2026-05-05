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
        return $this->belongsToMany(Hashtag::class, 'post_hashtags');
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

    public function getFormattedContentAttribute()
    {
        if (empty($this->content)) {
            return '';
        }

        // 1. Chống hack XSS (Quá chuẩn rồi!)
        $escapedContent = htmlspecialchars($this->content, ENT_QUOTES, 'UTF-8');

        // 2. Giữ nguyên định dạng xuống dòng
        $nl2brContent = nl2br($escapedContent);

        // 3. Regex an toàn hơn: 
        // Nhóm 1 ($1): Bắt khoảng trắng hoặc đầu dòng (^|\s)
        // Nhóm 2 ($2): Bắt nội dung hashtag (chữ, số, gạch dưới)
        $regex = '/(^|\s)#([\p{L}\p{N}_]+)/u';

        // Xài url() để link luôn đúng dù Pro có đổi tên miền hay thư mục
        $hashtagUrl = url('/hashtag');

        // Thay thế: giữ lời khoảng trắng ($1), bọc thẻ <a> cho hashtag ($2)
        $replacement = '$1<a href="' . $hashtagUrl . '?q=$2" class="hashtag-link">#$2</a>';

        return preg_replace($regex, $replacement, $nl2brContent);
    }
    
}
