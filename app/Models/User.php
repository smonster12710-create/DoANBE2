<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Post;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'fullname',
        'gender',
        'phone',
        'bio',
        'birthday',
        'address',
        'avatar_url',
        'cover_url',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id', 'id');
    }
    public function savedPosts()
    {
        return $this->belongsToMany(Post::class, 'saved_posts')
            ->withTimestamps();
    }

    public function followings()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id');
    }

    // Hàm kiểm tra xem mình đã follow người này chưa
    public function isFollowing($userId)
    {
        return $this->followings()->where('following_id', (int)$userId)->exists();
    }

    public function sentFriendRequests()
    {
        return $this->belongsToMany(User::class, 'friendships', 'user_id', 'friend_id')
                    ->withPivot('status')->withTimestamps();
    }

    /**
     * Lời mời kết bạn nhận về (đang chờ đồng ý)
     */
    public function receivedFriendRequests()
    {
        return $this->belongsToMany(User::class, 'friendships', 'friend_id', 'user_id')
                    ->withPivot('status')->withTimestamps();
    }

    /**
     * LẤY TRẠNG THÁI MỐI QUAN HỆ GIỮA MÌNH VÀ NGƯỜI KHÁC
     * (Hàm này trực tiếp sửa lỗi sập trang BadMethodCallException bạn đang gặp)
     */
    public function getFriendshipStatus($userId)
    {
        // Kiểm tra xem mình có gửi lời mời cho họ chưa
        $sent = $this->sentFriendRequests()->where('friend_id', $userId)->first();
        if ($sent) {
            return $sent->pivot->status; // Trả về 'pending' hoặc 'accepted'
        }

        // Kiểm tra xem họ có gửi lời mời cho mình chưa
        $received = $this->receivedFriendRequests()->where('user_id', $userId)->first();
        if ($received) {
            return $received->pivot->status === 'pending' ? 'requested' : 'accepted'; 
            // 'requested' nghĩa là đối phương đang chờ mình đồng ý
        }

        return 'none'; // Chưa có quan hệ gì cả
    }
}
