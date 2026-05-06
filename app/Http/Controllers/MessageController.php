<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;

class MessageController extends Controller
{
    public function index()
    {
        $myId = Auth::id();

        $conversations = Conversation::whereHas('participants', function ($query) use ($myId) {
            $query->where('user_id', $myId);
        })
            ->with(['lastMessage', 'participants.user'])
            ->get();

        $posts = Post::with([
            'user',
            'media',
            'likes',
            'comments'
        ])->latest()->get();

        return view('social.list_messages', compact('conversations', 'posts'));
    }

    public function show($id)
    {
        $myId = Auth::id();

        // Danh sách chat bên trái
        $conversations = Conversation::whereHas('participants', function ($q) use ($myId) {
            $q->where('user_id', $myId);
        })
            ->with(['lastMessage', 'participants.user'])
            ->get();

        // Chỉ lấy chat mà user hiện tại có quyền xem
        $currentChat = Conversation::whereHas('participants', function ($q) use ($myId) {
            $q->where('user_id', $myId);
        })
            ->with([
                'messages.sender',
                'participants.user'
            ])
            ->find($id);

        if (!$currentChat) {
            return redirect()
                ->route('list_messages')
                ->with('error', 'Bạn không có quyền xem cuộc trò chuyện này.');
        }

        // Người chat cùng
        // Xử lý private / group
        if ($currentChat->type === 'private') {
            $activeParticipant = $currentChat->participants
                ->where('user_id', '!=', $myId)
                ->first();

            $activePartner = $activeParticipant?->user;
        } else {
            $activePartner = null;
        }

        return view('social.chat_messages', [
            'conversations' => $conversations,
            'messages'      => $currentChat->messages,
            'activePartner' => $activePartner,
            'conversation'  => $currentChat
        ]);
    }
}
