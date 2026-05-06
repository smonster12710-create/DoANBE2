<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\Message;

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

        // 1. Danh sách chat bên trái (Giữ nguyên)
        $conversations = Conversation::whereHas('participants', function ($q) use ($myId) {
            $q->where('user_id', $myId);
        })
            ->with(['lastMessage', 'participants.user'])
            ->get();

        // 2. Lấy thông tin cuộc trò chuyện hiện tại
        $currentChat = Conversation::whereHas('participants', function ($q) use ($myId) {
            $q->where('user_id', $myId);
        })
            ->with(['participants.user'])
            ->find($id);

        if (!$currentChat) {
            return redirect()->route('list_messages')->with('error', 'Không có quyền xem.');
        }

        // 3. LẤY 50 TIN NHẮN MỚI NHẤT (Sửa ở đây)
        $messages = Message::where('conversation_id', $id)
            ->with('sender')
            ->orderBy('id', 'desc') // Lấy từ mới nhất trở về sau
            ->take(50)              // Lấy 50 cái
            ->get()
            ->sortBy('id');         // Đảo ngược lại để hiện thị đúng thứ tự thời gian (cũ trên mới dưới)

        // 4. Xử lý partner (Giữ nguyên)
        $activePartner = null;
        if ($currentChat->type === 'private') {
            $activeParticipant = $currentChat->participants->where('user_id', '!=', $myId)->first();
            $activePartner = $activeParticipant?->user;
        }

        return view('social.chat_messages', [
            'conversations' => $conversations,
            'messages'      => $messages, // Đây là 50 tin mới nhất đã được sắp xếp lại
            'activePartner' => $activePartner,
            'conversation'  => $currentChat
        ]);
    }
    public function send(Request $request)
    {
        $request->validate([
            'content' => 'required|string|min:1', // content không được null, phải là chuỗi, ít nhất 1 ký tự
            'conversation_id' => 'required|exists:conversations,id'
        ]);
        Message::create([
            'conversation_id' => $request->conversation_id,
            'sender_id' => Auth::id(),
            'content' => $request->content
        ]);

        return response()->json(['status' => 'ok']);
    }
    public function fetch($conversationId)
    {
        $lastId = request('last_id', 0);

        $messages = Message::with('sender')
            ->where('conversation_id', $conversationId)
            ->where('id', '>', $lastId)
            ->whereNotNull('content') //  Không lấy tin nhắn null
            ->where('content', '!=', '') // Không lấy tin nhắn rỗng
            ->orderBy('id')
            ->get();

        return response()->json($messages);
    }
    public function loadOlder(Request $request, $conversationId)
    {
        $firstId = $request->query('first_id');

        $messages = Message::with('sender')
            ->where('conversation_id', $conversationId)
            ->where('id', '<', $firstId) // Lấy những tin cũ hơn tin đầu tiên hiện tại
            ->orderBy('id', 'desc')      // Lấy từ tin gần nhất ngược về quá khứ
            ->take(20)                   // Mỗi lần lấy 20 tin
            ->get()
            ->sortBy('id')               // Đảo lại để chèn vào HTML cho đúng thứ tự
            ->values();

        return response()->json($messages);
    }
}
