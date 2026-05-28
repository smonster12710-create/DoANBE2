<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class Conversation extends Model
{
    protected $fillable = [
        'name',
        'image_url',
        'type'
    ];

    // Danh sách thành viên
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants', 'conversation_id', 'user_id')
            ->withPivot('role', 'joined_at');
    }

    // Tin nhắn
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    // Tin nhắn cuối
    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class, 'conversation_id')
            ->latestOfMany();
    }

    // Người chat cùng (Ép kiểu dữ liệu để lọc chính xác tuyệt đối)
    public function getPartnerAttribute()
    {
        $myId = (int) Auth::id(); // Ép ID của mình về kiểu Số nguyên

        // Dùng filter để ép kiểu ID của từng member về Số nguyên trước khi so sánh
        return $this->participants
            ->filter(function ($user) use ($myId) {
                return (int) $user->id !== $myId;
            })
            ->first();
    }

    // Kiểm tra user thuộc conversation
    public function hasParticipant($userId): bool
    {
        $targetId = (int) $userId; // Ép ID cần kiểm tra về kiểu Số nguyên

        return $this->participants
            ->filter(function ($user) use ($targetId) {
                return (int) $user->id === $targetId;
            })
            ->isNotEmpty();
    }
    // Lấy tin nhắn cuối cùng mà user này có thể thấy (chưa xoá)
    public function lastVisibleMessage($userId)
    {
        return $this->messages()
            ->whereDoesntHave('deletedMessages', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->latest()
            ->first();
    }
}
