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
}
