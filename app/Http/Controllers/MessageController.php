<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\Message;
use App\Models\DeletedMessage;

class MessageController extends Controller
{
    public function index()
    {
        $myId = Auth::id();

        $conversations = Conversation::whereHas('participants', function ($query) use ($myId) {
            $query->where('user_id', $myId);
        })
            ->with(['lastMessage', 'participants.user'])
            ->withCount([
                'messages as unread_count' => function ($query) use ($myId) {
                    $query->where('sender_id', '!=', $myId)
                        ->where('is_read', 0);
                }
            ])
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
            ->withCount([
                'messages as unread_count' => function ($query) use ($myId) {
                    $query->where('sender_id', '!=', $myId)
                        ->where('is_read', 0);
                }
            ])
            ->get();

        // 2. Lấy thông tin cuộc trò chuyện hiện tại
        $currentChat = Conversation::whereHas('participants', function ($q) use ($myId) {
            $q->where('user_id', $myId);
        })
            ->with(['participants.user'])
            ->where('id', $id)
            ->first();

        if (!$currentChat) {
            return redirect('/list_messages')
                ->with('error', 'Lỗi không thể truy cập!');
        }

        // 3. LẤY 50 TIN NHẮN MỚI NHẤT 
        $myId = Auth::id();

        // đánh dấu đã đọc
        Message::where('conversation_id', $id)
            ->where('sender_id', '!=', $myId)
            ->where('is_read', 0)
            ->update([
                'is_read' => 1
            ]);

        $messages = Message::where('conversation_id', $id)
            ->whereNotIn('id', function ($query) use ($myId) {
                $query->select('message_id')
                    ->from('deleted_messages')
                    ->where('user_id', $myId);
            })
            ->with('sender')
            ->orderBy('id', 'desc')
            ->take(50)
            ->get()
            ->sortBy('id'); // Đảo ngược lại để hiện thị đúng thứ tự thời gian (cũ trên mới dưới)

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
        $myId = Auth::id();

        $messages = Message::with('sender')
            ->where('conversation_id', $conversationId)
            ->where('id', '>', $lastId)

            ->whereNotIn('id', function ($query) use ($myId) {
                $query->select('message_id')
                    ->from('deleted_messages')
                    ->where('user_id', $myId);
            })

            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->orderBy('id')
            ->get();

        return response()->json($messages);
    }
    public function loadOlder(Request $request, $conversationId)
    {
        $firstId = $request->query('first_id');
        $myId = Auth::id();

        $messages = Message::with('sender')
            ->where('conversation_id', $conversationId)
            ->where('id', '<', $firstId)

            ->whereNotIn('id', function ($query) use ($myId) {
                $query->select('message_id')
                    ->from('deleted_messages')
                    ->where('user_id', $myId);
            })

            ->orderBy('id', 'desc')
            ->take(20)
            ->get()
            ->sortBy('id')
            ->values();

        return response()->json($messages);
    }
    public function recall($id)
    {
        $message = Message::findOrFail($id);

        // chỉ chủ tin nhắn được thu hồi
        if ($message->sender_id != Auth::id()) {

            return response()->json([
                'error' => 'Không có quyền'
            ], 403);
        }

        $message->is_deleted = 1;
        $message->save();

        return response()->json([
            'success' => true
        ]);
    }
    public function getConversations($id)
    {
        $myId = Auth::id();

        $conversations = Conversation::whereHas('participants', function ($query) use ($myId) {
            $query->where('user_id', $myId);
        })
            ->with(['lastMessage', 'participants.user'])
            ->withCount([
                'messages as unread_count' => function ($query) use ($myId) {
                    $query->where('sender_id', '!=', $myId)
                        ->where('is_read', 0);
                }
            ])
            ->get()
            ->sortByDesc(function ($chat) {
                return $chat->lastMessage->created_at ?? $chat->created_at;
            });

        return view('partials.list_chat', [
            'conversations' => $conversations,
            'conversation'  => (object)['id' => $id] // Truyền ID này để file blade so sánh và thêm class 'active-chat'
        ])->render();
    }
    public function deleteForMe($id)
    {
        DeletedMessage::firstOrCreate([
            'message_id' => $id,
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'success' => true
        ]);
    }
    public function deletedBy()
    {
        return $this->hasMany(DeletedMessage::class);
    }
    public function readStatus($conversationId)
    {
        $messages = Message::where(
            'conversation_id',
            $conversationId
        )
            ->where('sender_id', Auth::id())
            ->select('id', 'is_read')
            ->get();

        return response()->json($messages);
    }
    public function markAsRead($conversationId)
    {
        Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read', 0)
            ->update([
                'is_read' => 1
            ]);

        return response()->json([
            'success' => true
        ]);
    }
}
