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
use App\Models\User;
use Illuminate\Support\Facades\DB;


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
            ->with(['participants']) // ĐÃ SỬA: Bỏ `.user` vì bản thân participants đã chứa thông tin User
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
        // Lấy các bài viết (bản tin mạng xã hội) hiển thị kèm theo trang list tin nhắn
        $posts = Post::with([
            'user',
            'media',
            'likes',
            'comments'
        ])->latest()->get();

        // 1. Lấy ID những người ĐÃ KẾT BẠN từ bảng friendships
        $friendIds = DB::table('friendships')
            ->where(function ($query) use ($myId) {
                $query->where('user_id', $myId)->orWhere('friend_id', $myId);
            })
            ->where('status', 'accepted')
            ->get()
            ->map(function ($row) use ($myId) {
                return $row->user_id == $myId ? $row->friend_id : $row->user_id;
            })
            ->toArray();

        // 2. Lấy ID những người CHƯA KẾT BẠN NHƯNG ĐÃ TỪNG NHẮN TIN (từ các cuộc trò chuyện private)
        $chattedUserIds = DB::table('conversation_participants')
            ->whereIn('conversation_id', function ($q) use ($myId) {
                // Lấy danh sách id các phòng chat private mà mình có tham gia
                $q->select('conversation_id')
                    ->from('conversation_participants')
                    ->join('conversations', 'conversations.id', '=', 'conversation_participants.conversation_id')
                    ->where('conversation_participants.user_id', $myId)
                    ->where('conversations.type', 'private');
            })
            ->where('user_id', '!=', $myId) // Loại chính mình ra
            ->pluck('user_id')
            ->toArray();

        // 3. Gộp 2 mảng ID lại, lọc bỏ các ID trùng nhau
        $allValidUserIds = array_unique(array_merge($friendIds, $chattedUserIds));

        // 4. Bốc thông tin User đổ vào biến $friends để gửi ra Modal
        $friends = User::whereIn('id', $allValidUserIds)->get();

        // Trả về view kèm bộ dữ liệu danh sách chat, bài viết và danh sách bạn bè
        return view('social.list_messages', compact('conversations', 'posts', 'friends'));
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
            ->with(['participants']) // ĐÃ SỬA: Bỏ `.user`
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
            ->with(['participants']) // ĐÃ SỬA: Bỏ `.user`
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
                ->where('id', '!=', $myId) // ĐÃ SỬA: participants giờ là tập hợp User nên check trực tiếp cột 'id'
                ->pluck('id')
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
            $activePartner = $currentChat->partner;
        }

        // 🔥 CẬP NHẬT: Gộp cả Bạn bè + Người đã từng nhắn tin tại trang chi tiết chat
        $friendIds = DB::table('friendships')
            ->where(function ($query) use ($myId) {
                $query->where('user_id', $myId)->orWhere('friend_id', $myId);
            })
            ->where('status', 'accepted')
            ->get()
            ->map(function ($row) use ($myId) {
                return $row->user_id == $myId ? $row->friend_id : $row->user_id;
            })
            ->toArray();

        $chattedUserIds = DB::table('conversation_participants')
            ->whereIn('conversation_id', function ($q) use ($myId) {
                $q->select('conversation_id')
                    ->from('conversation_participants')
                    ->join('conversations', 'conversations.id', '=', 'conversation_participants.conversation_id')
                    ->where('conversation_participants.user_id', $myId)
                    ->where('conversations.type', 'private');
            })
            ->where('user_id', '!=', $myId)
            ->pluck('user_id')
            ->toArray();

        $allValidUserIds = array_unique(array_merge($friendIds, $chattedUserIds));
        $friends = User::whereIn('id', $allValidUserIds)->get();

        // Trả dữ liệu ra màn hình chat chính thức
        return view('social.chat_messages', [
            'conversations' => $conversations,
            'messages'      => $messages,
            'activePartner' => $activePartner,
            'conversation'  => $currentChat,
            'friends'       => $friends
        ]);
    }

    /**
     * Xử lý hành động bấm nút GỬI TIN NHẮN (Gọi qua AJAX API từ giao diện Chat)
     */
    public function send(Request $request)
    {
        // BƯỚC 1: KIỂM TRA DỮ LIỆU ĐẦU VÀO (VALIDATION)
        // - nới rộng dung lượng tối đa lên 20MB (20480 KB) để nhận được ảnh gốc dung lượng lớn từ điện thoại.
        // - Bổ sung thêm các định dạng ảnh: jfif, webp và các đuôi viết hoa như JPG, PNG để tránh lỗi chặn file.
        $request->validate([
            'content' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,jfif,webp,svg,JPG,PNG|max:20480',
            'conversation_id' => 'required|exists:conversations,id'
        ]);

        // BƯỚC 2: CHẶN TIN NHẮN RỖNG
        // Nếu người dùng không gõ chữ nào (sau khi đã cắt khoảng trắng bằng trim) 
        // ĐỒNG THỜI cũng không bấm chọn tải ảnh lên, thì trả về lỗi 422 ngay lập tức.
        if (
            empty(trim($request->content ?? ''))
            && !$request->hasFile('image')
        ) {
            return response()->json([
                'error' => 'Tin nhắn trống'
            ], 422);
        }

        // Biến lưu trữ đường dẫn ảnh sau khi xử lý thành công để lưu vào Database
        // Biến lưu trữ đường dẫn ảnh sau khi xử lý thành công để lưu vào Database
        $imagePath = null;

        // BƯỚC 3: XỬ LÝ NÉN VÀ ĐỔI ĐUÔI THÀNH PNG (BẢO HIỂM CHỐNG LỖI HTML CHO ẢNH LỚN)
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            // Cách 1: Nếu server có thư viện GD, tiến hành co nhỏ + nén ảnh lớn
            if (extension_loaded('gd')) {
                try {
                    $realPath = $file->getRealPath();
                    $extension = strtolower($file->getClientOriginalExtension());
                    $sourceImage = false;

                    switch ($extension) {
                        case 'jpg':
                        case 'jpeg':
                        case 'jfif':
                            $sourceImage = @imagecreatefromjpeg($realPath);
                            break;
                        case 'png':
                            $sourceImage = @imagecreatefrompng($realPath);
                            break;
                        case 'webp':
                            $sourceImage = @imagecreatefromwebp($realPath);
                            break;
                        case 'gif':
                            $sourceImage = @imagecreatefromgif($realPath);
                            break;
                    }

                    if ($sourceImage !== false) {
                        // Tự động xoay ảnh lớn đúng góc nếu có dữ liệu EXIF
                        if (extension_loaded('exif') && function_exists('exif_read_data')) {
                            $exif = @exif_read_data($realPath);
                            if (!empty($exif['Orientation'])) {
                                switch ($exif['Orientation']) {
                                    case 8:
                                        $sourceImage = imagerotate($sourceImage, 90, 0);
                                        break;
                                    case 3:
                                        $sourceImage = imagerotate($sourceImage, 180, 0);
                                        break;
                                    case 6:
                                        $sourceImage = imagerotate($sourceImage, -90, 0);
                                        break;
                                }
                            }
                        }

                        // 🌟 ÉP KÍCH THƯỚC: Ảnh to mấy cũng bóp về chiều rộng tối đa 1000px để giảm dung lượng
                        $origWidth  = imagesx($sourceImage);
                        $origHeight = imagesy($sourceImage);
                        $maxWidth   = 1000;

                        if ($origWidth > $maxWidth) {
                            $newWidth  = $maxWidth;
                            $newHeight = floor($origHeight * ($maxWidth / $origWidth));
                            $tmpImage = imagecreatetruecolor($newWidth, $newHeight);
                            imagealphablending($tmpImage, false);
                            imagesavealpha($tmpImage, true);
                            imagecopyresampled($tmpImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
                            imagedestroy($sourceImage);
                            $sourceImage = $tmpImage;
                        }

                        // Đặt tên file và tạo thư mục lưu trữ
                        $filename = time() . '_' . uniqid() . '.png';
                        $destinationPath = storage_path('app/public/chat_images');

                        if (!file_exists($destinationPath)) {
                            Mkdir($destinationPath, 0755, true);
                        }

                        // Nén ảnh PNG ở mức 6 (Mức tối ưu dung lượng)
                        imagepng($sourceImage, $destinationPath . '/' . $filename, 6);
                        imagedestroy($sourceImage);

                        $imagePath = 'chat_images/' . $filename;
                    } else {
                        // Nếu không đọc được ảnh bằng GD, lưu file gốc để chống sập tính năng
                        $imagePath = $file->store('chat_images', 'public');
                    }
                } catch (\Exception $e) {
                    // Nếu quá trình xử lý ảnh lớn bị lỗi bộ nhớ, lập tức cứu nguy bằng cách lưu file gốc
                    $imagePath = $file->store('chat_images', 'public');
                }
            } else {
                // Server không bật GD -> Dùng luôn hàm store gốc của Laravel
                $imagePath = $file->store('chat_images', 'public');
            }
        }

        // BƯỚC 4: TẠO BẢN GHI TIN NHẮN TRONG DATABASE
        // Lưu các thông tin: ID cuộc trò chuyện, ID người gửi, nội dung chữ và đường dẫn ảnh (.png đã nén)
        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'sender_id' => Auth::id(),
            'content' => $request->content,
            'image_url' => $imagePath
        ]);

        // BƯỚC 5: KÍCH HOẠT WEBSOCKET PHÁT TIN NHẮN REAL-TIME
        // Lấy thông tin cuộc trò chuyện kèm theo danh sách những người tham gia (participants)
        $conversation = Conversation::with('participants')->find($request->conversation_id);

        // Lọc ra danh sách ID của những người nhận (loại trừ chính bản thân mình ra)
        $receiverIds = $conversation
            ->participants
            ->where('id', '!=', Auth::id()) // So sánh trực tiếp với ID của User đang đăng nhập
            ->values()                      // Reset lại các key index của mảng sau khi dùng filter
            ->pluck('id')                   // Chỉ lấy duy nhất cột 'id'
            ->toArray();                    // Chuyển kết quả từ Collection về mảng PHP thuần

        // Gộp ID người nhận và ID chính mình thành mảng danh sách cần truyền tin qua WebSocket
        $broadcastIds = array_merge($receiverIds, [Auth::id()]);

        // Kích hoạt Event bắn dữ liệu qua kênh WebSocket (.toOthers() giúp người gửi không bị nhận lại sự kiện này)
        broadcast(new MessageSent($message, $broadcastIds))->toOthers();

        // BƯỚC 6: TRẢ PHẢN HỒI THÀNH CÔNG VỀ CHO CLIENT
        // Frontend (Javascript/AJAX) nhận được trạng thái này sẽ lập tức vẽ tin nhắn mới lên màn hình người gửi.
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
        $conversation = Conversation::with('participants')->find($message->conversation_id);
        $broadcastIds = $conversation->participants->pluck('id')->toArray(); // ĐÃ SỬA: pluck('id') thay vì user_id
        broadcast(new MessageSent($message, $broadcastIds))->toOthers();

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
            ->with(['participants']) // ĐÃ SỬA: Bỏ `.user`
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
    public function deleteForMe($id)
    {
        $myId = Auth::id();

        $deleted = DeletedMessage::firstOrCreate([
            'message_id' => $id,
            'user_id' => $myId
        ]);

        $message = Message::find($id);
        if ($message) {
            $conversationId = $message->conversation_id;


            $conversation = Conversation::find($conversationId);

            // Bốc ngay tin nhắn cuối cùng thực sự còn hiển thị sau khi đã xóa tin hiện tại
            $nextLastMessage = $conversation->lastVisibleMessage($myId);

            if ($nextLastMessage) {
                // Nếu còn tin nhắn cũ hơn, bắn Event gối đầu để Sidebar cập nhật lại chữ
                // Chỉ bắn cho CHÍNH MÌNH (mảng [$myId]) vì người khác không xóa tin này
                broadcast(new MessageSent($nextLastMessage, [$myId]));
            } else {
                // Nếu xóa sạch sành sanh không còn tin nào, tạo một tin nhắn giả lập rỗng
                $fakeMessage = new Message();
                $fakeMessage->conversation_id = $conversationId;
                $fakeMessage->content = '';
                $fakeMessage->sender_id = $myId;

                broadcast(new MessageSent($fakeMessage, [$myId]));
            }
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
                    ->where('id', '!=', $myId) // ĐÃ SỬA: lọc theo cột id trực tiếp
                    ->pluck('id')
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

    /**
     * Tạo hoặc tìm kiếm cuộc trò chuyện khi click nút "Nhắn tin" từ Profile
     */
    public function startChat($username)
    {
        $receiver = User::where('username', $username)->firstOrFail();
        $senderId = auth()->id();

        // Khóa trường hợp tự nhắn tin cho chính mình
        if ($receiver->id === $senderId) {
            return redirect()->back()->with('error', 'Bạn không thể tự nhắn tin cho bản thân.');
        }

        // Tìm cuộc hội thoại 'private' mà CẢ HAI người cùng tham gia
        $conversation = Conversation::where('type', 'private')
            ->whereHas('participants', function ($query) use ($senderId) {
                $query->where('user_id', $senderId);
            })
            ->whereHas('participants', function ($query) use ($receiver) {
                $query->where('user_id', $receiver->id);
            })
            ->first();

        // Nếu chưa từng chat (chưa có cuộc hội thoại private chung) thì tạo mới
        if (!$conversation) {
            $conversation = DB::transaction(function () use ($senderId, $receiver) {
                // 1. Tạo phòng chat private
                $newConversation = Conversation::create([
                    'type' => 'private',
                    'name' => null,
                ]);

                // 2. Thêm cả 2 user vào bảng conversation_participants
                $newConversation->participants()->attach([
                    $senderId => ['role' => 'member'],
                    $receiver->id => ['role' => 'member']
                ]);

                return $newConversation;
            });
        }

        // Chuyển hướng về trang chat với ID cuộc hội thoại vừa tìm được hoặc vừa tạo
        return redirect()->route('chat_messages', $conversation->id);
    }
    /**
     * 🔥 HÀM XỬ LÝ TẠO NHÓM CHAT MỚI QUA AJAX
     */
    public function createGroup(Request $request)
    {
        $request->validate([
            'group_name'   => 'required|string|max:255',
            'user_ids'     => 'required|array|min:1',
            'user_ids.*'   => 'exists:users,id',
            'group_avatar' => 'nullable|image|max:2048',
        ]);

        $myId = auth()->id();

        try {
            $conversation = DB::transaction(function () use ($request, $myId) {

                $avatarPath = null;
                if ($request->hasFile('group_avatar')) {
                    // Lưu ảnh đại diện nhóm vào thư mục group_avatars công khai
                    $avatarPath = $request->file('group_avatar')->store('group_avatars', 'public');
                }

                // 🔥 ĐÃ ĐỒNG BỘ: Đổi tên key thành 'image_url' cho đúng tên cột DB của cậu
                $newChat = Conversation::create([
                    'type'      => 'group',
                    'name'      => $request->group_name,
                    'image_url' => $avatarPath,
                ]);

                $members = [];
                $members[$myId] = ['role' => 'admin'];

                foreach ($request->user_ids as $id) {
                    if ($id != $myId) {
                        $members[$id] = ['role' => 'member'];
                    }
                }

                $newChat->participants()->attach($members);

                return $newChat;
            });

            return response()->json([
                'success' => true,
                'message' => 'Tạo nhóm chat thành công! 🎉',
                'room_id' => $conversation->id
            ]);
        } catch (\Exception $e) {
            \Log::error('Lỗi tạo nhóm chat: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra trên máy chủ, cậu thử lại nhé!'
            ], 500);
        }
    }
}
