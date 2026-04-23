<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth; // Để fix lỗi auth()->id()
use App\Models\Message; // Model tin nhắn
use App\Models\ConversationParticipant; // Model người tham gia

class Conversation extends Model
{
    // 1. Quan hệ lấy danh sách người tham gia
    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class, 'conversation_id');
    }

    // 2. Quan hệ lấy tin nhắn
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    // 3. Lấy tin nhắn mới nhất để hiện ở List Chat
    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class, 'conversation_id')->latest();
    }

    // 4. Accessor lấy thông tin người chat cùng (Partner)
    // public function getPartnerAttribute()
    // {
    //     $me = Auth::id(); // Dùng Auth::id() thay cho auth()->id()

    //     $participant = $this->participants()
    //         ->where('user_id', '!=', $me)
    //         ->first();

    //     return $participant ? $participant->user : null;
    // }
    // Trong file app/Models/Conversation.php
    public function getPartnerAttribute()
    {
        $me = 1; // ÉP CỨNG ID LÀ 1 (ID của bạn trong database)

        $participant = $this->participants()
            ->where('user_id', '!=', $me)
            ->first();

        return $participant ? $participant->user : null;
    }
}
