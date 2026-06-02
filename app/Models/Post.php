<?php

namespace App\Models;

use App\Models\Block;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Prunable; // 1. Import Trait Prunable
use App\Models\PostMedia;

class Post extends Model
{
    use HasFactory, Prunable; // 2. Sử dụng Trait Prunable ở đây

    protected $fillable = [
        'user_id',
        'content',
        'image_url',
        'video_url',
        'expires_at', // 3. Thêm cột này vào fillable để cho phép lưu dữ liệu
        'group_id' // ✨ THÊM DÒNG NÀY: Cho phép lưu ID của nhóm vào bài viết
    ];

    protected $with = ['user'];

    // --- LOGIC TỰ ĐỘNG XÓA BÀI VIẾT (PRUNABLE) ---

    /**
     * Xác định điều kiện những bài viết nào sẽ bị đưa vào danh sách xóa tự động.
     */
    public function prunable()
    {
        // Tìm các bài viết có cài ngày hết hạn và thời gian đó nhỏ hơn hoặc bằng thời gian hiện tại
        return static::whereNotNull('expires_at')->where('expires_at', '<=', now());
    }

    /**
     * Hành động dọn dẹp trước khi bài viết chính thức biến mất khỏi Database.
     */
    protected function pruning()
    {
        // 1. Duyệt qua danh sách media để xóa file ảnh vật lý trong thư mục public/uploads/posts
        foreach ($this->media as $mediaItem) {
            $filePath = public_path($mediaItem->media_url);
            if (file_exists($filePath)) {
                @unlink($filePath); // Xóa file cứng trên server
            }
            $mediaItem->delete(); // Xóa dòng dữ liệu trong bảng post_media
        }

        // 2. Dọn dẹp các bảng liên quan (Đề phòng trường hợp database không setup cascade delete)
        $this->comments()->delete();   // Xóa bình luận của bài viết này
        $this->likes()->delete();      // Xóa các lượt like liên quan
        $this->hashtags()->detach();   // Gỡ liên kết trong bảng trung gian post_hashtags
        $this->savedByUsers()->detach(); // Gỡ liên kết lưu bài viết trong bảng saved_posts
    }


    // --- CÁC MỐI QUAN HỆ (RELATIONS) GIỮ NGUYÊN ---

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
    }

    /**
     * Mối quan hệ với Hashtag qua bảng trung gian post_hashtags
     */
    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'post_hashtags');
    }

    /**
     * Mối quan hệ lấy các ảnh/video đính kèm bài viết
     */
    public function media()
    {
        return $this->hasMany(PostMedia::class, 'post_id');
    }

    /**
     * Mối quan hệ lấy bình luận bài viết
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Danh sách những người dùng đã lưu bài viết này
     */
    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'saved_posts')
            ->withTimestamps();
    }

    /**
     * Lấy thông tin bài viết gốc (nếu bài hiện tại là bài share)
     */
    public function parent()
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    /**
     * Tương tự hàm parent, hỗ trợ gọi theo tên sharedPost
     */
    public function sharedPost()
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    // Trong app/Models/Post.php
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('notBlocked', function ($builder) {
            // Kiểm tra user đã đăng nhập chưa
            if (auth()->check()) {
                $userId = auth()->id();

                // Lấy danh sách ID người mình chặn và mình đã chặn
                $blockedIds = \App\Models\Block::where('blocker_id', $userId)->pluck('blocked_id');
                $blockerIds = \App\Models\Block::where('blocked_id', $userId)->pluck('blocker_id');

                $excluded = $blockedIds->merge($blockerIds)->unique();

                // Dùng posts.user_id để tránh lỗi 'Ambiguous column'
                $builder->whereNotIn('posts.user_id', $excluded);
            }
        });
    }
}
