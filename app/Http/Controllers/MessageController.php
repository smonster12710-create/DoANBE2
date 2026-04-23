<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation; // Nhớ use Model vào nhé
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        // 1. Lấy ID của mình
        $myId = Auth::id();

        // 2. Lấy các cuộc hội thoại mà mình có tham gia
        $conversations = Conversation::whereHas('participants', function ($query) use ($myId) {
            $query->where('user_id', $myId);
        })
            ->with(['lastMessage', 'participants.user']) // Load sẵn tin nhắn cuối và thông tin người kia
            ->get();

        // 3. Đẩy biến $conversations sang view list_messages
        return view('social.list_messages', compact('conversations'));
    }
    public function show($id)
    {
        $myId = auth()->id();

        // 1. Danh sách bên trái
        $conversations = Conversation::whereHas('participants', function ($q) use ($myId) {
            $q->where('user_id', $myId);
        })->with(['lastMessage', 'participants.user'])->get();

        // 2. Phòng chat hiện tại
        $currentChat = Conversation::with(['messages', 'participants.user'])->findOrFail($id);

        // 3. Người đang chat cùng (Partner thực sự của phòng này)
        $activePartner = $currentChat->participants->where('user_id', '!=', $myId)->first()->user
            ?? $currentChat->participants->first()->user;

        return view('social.chat_messages', [
            'conversations' => $conversations,
            'messages'      => $currentChat->messages,
            'activePartner' => $activePartner, // Dùng cái này cho Header
            'conversation'  => $currentChat
        ]);
    }
}
