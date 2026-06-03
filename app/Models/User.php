<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Post;
use Illuminate\Support\Str;

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
        'show_activity_status',
        'profile_locked',
        'is_online',
        'last_activity_at',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'show_activity_status' => 'boolean',
        'profile_locked' => 'boolean',
        'is_online' => 'boolean',
        'last_activity_at' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Khi luu avatar, neu du lieu cu/gui len dang URL local thi doi ve path tuong doi.
    public function setAvatarUrlAttribute($value)
    {
        $this->attributes['avatar_url'] = $this->normalizeLocalImagePath($value);
    }

    // Khi luu cover, chi giu path anh local de khong bi dinh cung theo domain/localhost.
    public function setCoverUrlAttribute($value)
    {
        $this->attributes['cover_url'] = $this->normalizeLocalImagePath($value);
    }

    // Dung trong Blade de hien thi avatar ma khong can goi asset() truc tiep.
    public function getAvatarSrcAttribute()
    {
        return $this->profileImageSrc($this->avatar_url, 'img/user/user.jpg');
    }

    // Dung trong Blade de hien thi cover ma khong phu thuoc APP_URL.
    public function getCoverSrcAttribute()
    {
        return $this->profileImageSrc($this->cover_url, 'img/cover/default-cover.jpg');
    }

    private function profileImageSrc($path, string $default): string
    {
        $path = $this->normalizeLocalImagePath($path) ?: $default;

        // Link anh ben ngoai nhu Dicebear/Picsum van duoc giu nguyen.
        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        // Anh local dung root-relative path de chay duoc ca localhost va hosting.
        return '/' . ltrim($path, '/');
    }

    private function normalizeLocalImagePath($path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = trim((string) $path);

        if (Str::startsWith($path, ['http://', 'https://'])) {
            $urlPath = parse_url($path, PHP_URL_PATH);

            // Chi strip domain voi anh local cua he thong, khong dung toi URL anh ngoai.
            if ($urlPath && Str::startsWith(ltrim($urlPath, '/'), ['uploads_profile/', 'img/'])) {
                return ltrim($urlPath, '/');
            }
        }

        return ltrim($path, '/');
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
    /**
     * Kiểm tra người dùng hiện tại có phải bạn bè với người xem hay không.
     * Dùng cho các chức năng cần quan hệ bạn bè:
     * - Trạng thái hoạt động
     * - Quyền xem trang cá nhân
     */
    public function isFriendWith($viewer)
    {
        if (!$viewer) {
            return false;
        }

        // Chính mình không tính là bạn bè để tránh tự thấy chấm xanh của mình
        if ($this->id == $viewer->id) {
            return false;
        }

        return $this->getFriendshipStatus($viewer->id) === 'accepted';
    }
    /**
     * Kiểm tra có được hiển thị chấm xanh hoạt động cho người xem hay không.
     *
     * Điều kiện:
     * - Người đang xem cũng bật trạng thái hoạt động
     * - Người được xem cũng bật trạng thái hoạt động
     * - Hai người là bạn bè
     */
    public function canShowActivityTo($viewer)
    {
        if (!$viewer) {
            return false;
        }

        // Chủ tài khoản được tự thấy trạng thái của mình khi đã bật hiển thị hoạt động.
        if ($this->id == $viewer->id) {
            return false;
        }

        return $this->show_activity_status == 1
            && $viewer->show_activity_status == 1
            && $this->isFriendWith($viewer);
    }

    /**
     * Moi quan he: mot nguoi dung co the tham gia nhieu hoi nhom khac nhau.
     */
    public function joinedGroups()
    {
        return $this->belongsToMany(Group::class, 'group_members')
            ->withPivot('role', 'status')
            ->withTimestamps();
    }

    public function blocks()
    {
        return $this->hasMany(Block::class, 'blocker_id');
    }

    public function isBlocking($userId)
    {
        return $this->blocks()->where('blocked_id', $userId)->exists();
    }

    public function activityStatusFor($viewer): array
    {
        if (!$this->canShowActivityTo($viewer)) {
            return $this->hiddenActivityPayload();
        }

        return $this->activityStatusPayload();
    }

    public function activityStatusPayload(): array
    {
        if (!$this->show_activity_status || !$this->last_activity_at) {
            return $this->hiddenActivityPayload();
        }

        $lastActivity = $this->last_activity_at;
        $secondsSinceActivity = $lastActivity->diffInSeconds(now());

        if ($this->is_online && $secondsSinceActivity <= 90) {
            return [
                'user_id' => (int) $this->id,
                'visible' => true,
                'status' => 'online',
                'last_activity_at' => $lastActivity->toIso8601String(),
                'label' => 'Dang hoat dong',
                'short_label' => 'Online',
            ];
        }

        if ($secondsSinceActivity <= 86400) {
            return [
                'user_id' => (int) $this->id,
                'visible' => true,
                'status' => 'away',
                'last_activity_at' => $lastActivity->toIso8601String(),
                'label' => 'Hoat dong ' . $this->shortActivityTime($secondsSinceActivity) . ' truoc',
                'short_label' => $this->shortActivityTime($secondsSinceActivity),
            ];
        }

        return $this->hiddenActivityPayload();
    }

    private function hiddenActivityPayload(): array
    {
        return [
            'user_id' => (int) $this->id,
            'visible' => false,
            'status' => 'hidden',
            'last_activity_at' => optional($this->last_activity_at)->toIso8601String(),
            'label' => '',
            'short_label' => '',
        ];
    }

    private function shortActivityTime(int $seconds): string
    {
        if ($seconds < 60) {
            return 'vua xong';
        }

        if ($seconds < 3600) {
            return floor($seconds / 60) . 'p';
        }

        return floor($seconds / 3600) . 'h';
    }
}


