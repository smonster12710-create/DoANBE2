<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Conversation extends Model
{
    protected $fillable = [
        'name',
        'image_url',
        'type'
    ];

    // Danh sách thành viên
    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class, 'conversation_id');
    }

    // Tin nhắn
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    // Tin nhắn cuối
    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class, 'conversation_id')->latestOfMany();
    }

    // Người chat cùng (chat private)
    public function getPartnerAttribute()
    {
        $myId = Auth::id();

        $participant = $this->participants()
            ->where('user_id', '!=', $myId)
            ->with('user')
            ->first();

        return $participant?->user;
    }

    // Kiểm tra user có thuộc conversation không
    public function hasParticipant($userId): bool
    {
        return $this->participants()
            ->where('user_id', $userId)
            ->exists();
    }
}