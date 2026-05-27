<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// 1. TRẠM GÁC 1: Kênh riêng tư theo từng User để nhảy số Badge Navbar Realtime
// (Gom 3 đoạn trùng lặp cũ về duy nhất 1 đoạn chuẩn chỉ này)
Broadcast::channel('user.{id}', function ($user, $id) {
    // Ép kiểu cả 2 bên về số nguyên (int) để tránh lỗi so sánh lệch kiểu dữ liệu
    return (int) $user->id === (int) $id;
});

// 2. TRẠM GÁC 2: Kênh riêng tư của từng phòng chat để nhận tin nhắn Realtime
Broadcast::channel('chat-conversation.{conversationId}', function ($user, $conversationId) {
    // Chỉ cho phép người dùng thực sự là thành viên (participant) của phòng chat này kết nối vào
    return Conversation::where('id', $conversationId)
        ->whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->exists();
});

