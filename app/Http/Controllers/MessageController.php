<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\Message;
use App\Models\DeletedMessage;
use App\Events\MessageSent; // Import sự kiện (Event) để kích hoạt trạm phát real-time
use App\Events\ChatReadStatusUpdated;

class MessageController extends Controller
{
    /**
     * Hiển thị danh sách toàn bộ cuộc trò chuyện của User (Trang tổng quan)
     */
    public function index()
    {
        $myId = Auth::id(); // Lấy ID của bản thân người đang đăng nhập

        // Lấy danh sách các cuộc trò chuyện mà bản thân tham gia
        $conversations = Conversation::whereHas('participants', function ($query) use ($myId) {
            $query->where('user_id', $myId);
        })
            ->with(['participants.user']) // Tải trước thông tin thành viên tránh lỗi N+1
            ->withCount([
                // Đếm số lượng tin nhắn chưa đọc từ người khác gửi tới (Đã loại trừ tin nhắn đã xóa 1 chiều)
                'messages as unread_count' => function ($query) use ($myId) {
                    $query->where('sender_id', '!=', $myId)
                        ->where('is_read', 0)
                        ->whereNotIn('id', function ($q) use ($myId) {
                            $q->select('message_id')->from('deleted_messages')->where('user_id', $myId);
                        });
                }
            ])
            ->get()
            ->map(function ($chat) use ($myId) {
                // Gắn tin nhắn cuối cùng chưa bị xoá để hiển thị ngoài danh sách sidebar
                $chat->last_visible_message = $chat->lastVisibleMessage($myId);
                return $chat;
            })
            // Sắp xếp cuộc trò chuyện có tin nhắn mới nhất lên đầu dựa trên last_visible_message
            ->sortByDesc(function ($chat) {
                return optional($chat->last_visible_message)->created_at ?? $chat->created_at;
            })
            ->values(); // Reset lại các key của mảng sau khi sắp xếp

        // Lấy các bài viết (bản tin mạng xã hội) hiển thị kèm theo trang list tin nhắn
        $posts = Post::with([
            'user',
            'media',
            'likes',
            'comments'
        ])->latest()->get();

        // Trả về view kèm bộ dữ liệu danh sách chat và bài viết
        return view('social.list_messages', compact('conversations', 'posts'));
    }

    /**
     * Vào xem nội dung chi tiết của một phòng chat cụ thể ($id cuộc trò chuyện)
     */
    public function show($id)
    {
        $myId = Auth::id();

        // 1. Lấy danh sách chat bên trái để hiển thị song song ở giao diện chat
        $conversations = Conversation::whereHas('participants', function ($q) use ($myId) {
            $q->where('user_id', $myId);
        })
            ->with(['participants.user'])
            ->withCount([
                'messages as unread_count' => function ($query) use ($myId) {
                    $query->where('sender_id', '!=', $myId)
                        ->where('is_read', 0)
                        ->whereNotIn('id', function ($q) use ($myId) {
                            $q->select('message_id')->from('deleted_messages')->where('user_id', $myId);
                        });
                }
            ])
            ->get()
            // Gắn tin nhắn cuối thật sự chưa bị xóa 1 chiều
            ->map(function ($chat) use ($myId) {
                $chat->last_visible_message = $chat->lastVisibleMessage($myId);
                return $chat;
            })
            // Sort cuộc trò chuyện mới nhất lên đầu dựa trên last_visible_message
            ->sortByDesc(function ($chat) {
                return optional($chat->last_visible_message)->created_at ?? $chat->created_at;
            })
            ->values();

        // 2. Lấy thông tin cuộc trò chuyện hiện tại đang mở
        $currentChat = Conversation::whereHas('participants', function ($q) use ($myId) {
            $q->where('user_id', $myId);
        })
            ->with(['participants.user'])
            ->where('id', $id)
            ->first();

        // Nếu phòng chat không tồn tại hoặc bản thân không thuộc phòng chat đó thì đá về trang tổng
        if (!$currentChat) {
            return redirect('/list_messages')
                ->with('error', 'Lỗi không thể truy cập!');
        }

        // 3. Đánh dấu ĐÃ ĐỌC cho toàn bộ tin nhắn đối phương gửi trong phòng chat này
        Message::where('conversation_id', $id)
            ->where('sender_id', '!=', $myId)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        // Lấy danh sách ID của những người đã gửi tin nhắn cho mình trong phòng này
        $senderIds = Message::where('conversation_id', $id)
            ->where('sender_id', '!=', $myId)
            ->pluck('sender_id')
            ->unique()
            ->values()
            ->toArray();

        // Nếu phòng chat mới tinh chưa ai nhắn gì, lấy đại diện thành viên khác trong phòng
        if (empty($senderIds)) {
            $senderIds = $currentChat->participants
                ->where('user_id', '!=', $myId)
                ->pluck('user_id')
                ->toArray();
        }

        // 🔥 ÉP PHÁT TÍN HIỆU NGAY: Kích hoạt chuông báo realtime sang máy bên kia
        broadcast(new ChatReadStatusUpdated($id, [], $senderIds))->toOthers();

        // 4. Lấy tối đa 50 tin nhắn mới nhất chưa bị xoá (ẩn) ở phía bản thân
        $messages = Message::where('conversation_id', $id)
            ->whereNotIn('id', function ($query) use ($myId) {
                $query->select('message_id')
                    ->from('deleted_messages')
                    ->where('user_id', $myId);
            })
            ->with('sender') // Lấy thông tin người gửi để hiện avatar/tên
            ->orderBy('id', 'desc') // Lấy tin nhắn mới nhất trước
            ->take(50)
            ->get()
            ->sortBy('id'); // Đảo ngược lại để đúng thứ tự hiển thị: Cũ ở trên, mới ở dưới

        // 5. Xác định đối phương chat cùng (chỉ áp dụng nếu là chat cá nhân 1-1)
        $activePartner = null;
        if ($currentChat->type === 'private') {
            $activeParticipant = $currentChat->participants->where('user_id', '!=', $myId)->first();
            $activePartner = $activeParticipant?->user;
        }

        // Trả dữ liệu ra màn hình chat chính thức
        return view('social.chat_messages', [
            'conversations' => $conversations,
            'messages'      => $messages,
            'activePartner' => $activePartner,
            'conversation'  => $currentChat
        ]);
    }

    /**
     * Xử lý hành động bấm nút GỬI TIN NHẮN (Gọi qua AJAX API)
     */
    public function send(Request $request)
    {
        // Kiểm tra dữ liệu đầu vào
        $request->validate([
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:2048', // Ảnh không quá 2MB
            'conversation_id' => 'required|exists:conversations,id'
        ]);

        // Chặn không cho phép bấm gửi một tin nhắn rỗng (không chữ, không ảnh)
        if (
            empty(trim($request->content ?? ''))
            && !$request->hasFile('image')
        ) {
            return response()->json([
                'error' => 'Tin nhắn trống'
            ], 422);
        }

        $imagePath = null;

        // Xử lý upload ảnh vào thư mục storage/chat_images nếu có đính kèm ảnh
        if ($request->hasFile('image')) {
            $imagePath = $request
                ->file('image')
                ->store('chat_images', 'public');
        }

        // Tạo bản ghi tin nhắn mới trong Database và gán vào biến $message
        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'sender_id' => Auth::id(),
            'content' => $request->content,
            'image_url' => $imagePath
        ]);

        // KÍCH HOẠT WEBSOCKET REAL-TIME:
        $conversation = Conversation::with('participants')->find($request->conversation_id);

        // lấy người nhận
        $receiverIds = $conversation
            ->participants
            ->where('user_id', '!=', Auth::id())
            ->values() // reset lại key sau khi lọc để đảm bảo không bị lỗi khi pluck
            ->pluck('user_id')
            ->toArray();
        $broadcastIds = array_merge($receiverIds, [Auth::id()]);
        broadcast(new MessageSent($message, $broadcastIds))->toOthers();

        // Trả phản hồi thành công về cho Javascript xử lý giao diện người gửi
        return response()->json([
            'status' => 'ok',
            'message' => $message
        ]);
    }

    /**
     * Lấy các tin nhắn mới (Dùng cho cơ chế Long-polling cũ)
     */
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
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('content')
                        ->where('content', '!=', '');
                })
                    ->orWhereNotNull('image_url');
            })
            ->orderBy('id')
            ->get();

        return response()->json($messages);
    }

    /**
     * Phân trang: Tải tin nhắn cũ hơn khi người dùng cuộn ngược lên trên đầu khung chat
     */
    public function loadOlder(Request $request, $conversationId)
    {
        $firstId = $request->query('first_id'); // ID của tin nhắn nằm cao nhất hiện tại
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

    /**
     * Thu hồi tin nhắn (Xoá ở cả 2 bên màn hình - `is_deleted = 1`)
     */
    public function recall($id)
    {
        $message = Message::findOrFail($id);

        // Bảo mật: Chỉ chủ nhân của tin nhắn đó mới có quyền bấm thu hồi
        if ($message->sender_id != Auth::id()) {
            return response()->json([
                'error' => 'Không có quyền'
            ], 403);
        }

        $message->is_deleted = 1;
        $message->save();

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Lấy lại mã HTML danh sách sidebar bên trái (Dùng khi AJAX muốn render lại sidebar)
     */
    public function getConversations($id)
    {
        $myId = Auth::id();

        $conversations = Conversation::whereHas('participants', function ($query) use ($myId) {
            $query->where('user_id', $myId);
        })
            ->with(['participants.user']) // Đã loại bỏ logic lấy bừa tin nhắn thô chưa lọc cũ
            ->withCount([
                'messages as unread_count' => function ($query) use ($myId) {
                    $query->where('sender_id', '!=', $myId)
                        ->where('is_read', 0)
                        ->whereNotIn('id', function ($q) use ($myId) {
                            $q->select('message_id')->from('deleted_messages')->where('user_id', $myId);
                        });
                }
            ])
            ->get()
            ->map(function ($chat) use ($myId) {
                // Ép sử dụng hàm chuẩn để bốc tin nhắn cuối thực sự chưa bị ẩn 1 chiều
                $chat->last_visible_message = $chat->lastVisibleMessage($myId);
                return $chat;
            })
            // Sắp xếp chuẩn xác theo thời gian tạo của last_visible_message đã lọc
            ->sortByDesc(function ($chat) {
                return optional($chat->last_visible_message)->created_at ?? $chat->created_at;
            })
            ->values();

        // Trả về trực tiếp khối HTML sau khi được render từ file partial view
        return view('partials.list_chat', [
            'conversations' => $conversations,
            'conversation'  => (object)['id' => $id]
        ])->render();
    }

    /**
     * Xoá tin nhắn ở phía một mình tôi (Chèn ID vào bảng trung gian `deleted_messages`)
     */
    /**
     * Xoá tin nhắn ở phía một mình tôi (Chèn ID vào bảng trung gian `deleted_messages`)
     */
    public function deleteForMe($id)
    {
        $deleted = DeletedMessage::firstOrCreate([
            'message_id' => $id,
            'user_id' => Auth::id()
        ]);

        // 🔥 BỔ SUNG: Tìm tin nhắn vừa xóa để lấy conversation_id
        $message = Message::find($id);
        if ($message) {
            // Phát tín hiệu thông báo trạng thái đọc/hoặc cập nhật sidebar cho chính mình
            // Truyền ID của mình vào mảng người nhận để máy mình tự nghe và update sidebar
            broadcast(new ChatReadStatusUpdated($message->conversation_id, [], [Auth::id()]))->toOthers();
        }

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Định nghĩa mối quan hệ HasMany
     */
    public function deletedBy()
    {
        return $this->hasMany(DeletedMessage::class);
    }

    /**
     * Lấy trạng thái Đã đọc / Chưa đọc của các tin nhắn mình đã gửi đi
     */
    public function readStatus($conversationId)
    {
        $messages = Message::where('conversation_id', $conversationId)
            ->where('sender_id', Auth::id())
            ->select('id', 'is_read')
            ->get();

        return response()->json($messages);
    }

    /**
     * API kích hoạt nhanh hành động đánh dấu toàn bộ phòng chat là đã đọc
     */
    public function markAsRead($conversationId)
    {
        $myId = auth()->id();

        // 1. Cập nhật tất cả tin nhắn của đối phương trong phòng này thành đã đọc (is_read = 1)
        Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $myId)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        // 2. Lấy ID của đối phương (người gửi tin nhắn cho mình) để biết phát tín hiệu cho ai
        $senderIds = Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $myId)
            ->pluck('sender_id')
            ->unique()
            ->values()
            ->toArray();

        // 3. Nếu không tìm thấy sender nào cụ thể (do chưa có tin nhắn), 
        // ta lấy đại diện toàn bộ thành viên trong phòng chat trừ chính mình ra
        if (empty($senderIds)) {
            $conversation = Conversation::with('participants')->find($conversationId);
            if ($conversation) {
                $senderIds = $conversation->participants
                    ->where('user_id', '!=', $myId)
                    ->pluck('user_id')
                    ->toArray();
            }
        }

        // 🔥 BỎ ĐIỀU KIỆN IF: Cứ gọi API thành công là ÉP PHÁT tín hiệu realtime đi ngay!
        broadcast(new ChatReadStatusUpdated($conversationId, [], $senderIds))->toOthers();

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Đếm TỔNG số lượng tin nhắn chưa đọc trên toàn hệ thống
     */
    public function unreadCount()
    {
        $userId = auth()->id();

        if (!$userId) {
            return response()->json(['count' => 0]);
        }

        $count = Message::whereHas('conversation.participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->where('sender_id', '!=', $userId)
            ->where('is_read', 0)
            ->count();

        return response()->json([
            'count' => (int)$count
        ]);
    }
}
