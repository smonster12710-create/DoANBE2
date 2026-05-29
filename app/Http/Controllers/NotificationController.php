<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /*
     * Hiển thị trang danh sách thông báo
     */
    public function index()
    {
        $notifications = \App\Models\Notification::with('actor')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('social.notifications', compact('notifications'));
    }

    /**
     * Lấy 5 thông báo mới nhất và đếm số lượng thông báo chưa đọc
     */
    public function getMyNotifications()
    {
        $userId = auth()->id();

        $notifications = \App\Models\Notification::with('actor')
            ->where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        $unreadCount = \App\Models\Notification::where('user_id', $userId)
            ->where('is_read', 0)
            ->count();

        // Sửa lại cú pháp compact
        return view('social.notifications', compact('notifications', 'unreadCount'));
    }

    /**
     * Đánh dấu tất cả thông báo đã đọc
     */

    public function markAsRead()
    {
        \App\Models\Notification::where('user_id', auth()->id())
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Xử lý khi người dùng click vào một thông báo cụ thể
     * - Kiểm tra nếu thông báo liên quan đến một bài viết đã bị xóa thì xóa luôn thông báo đó và trả về lỗi
     * - Nếu không thì đánh dấu đã đọc và trả về URL để frontend chuyển hướng
     */
    public function readSingle($id)
    {
        $notification = \App\Models\Notification::find($id);

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Thông báo không tồn tại!']);
        }

        // Đánh dấu đã đọc
        if ($notification->user_id == auth()->id() && $notification->is_read == 0) {
            $notification->update(['is_read' => 1]);
        }

        $redirectUrl = '';
        $type = strtolower(trim($notification->type));

        switch ($type) {
            // ==========================================
            // NHÓM BÀI VIẾT: Nhảy tới trang chi tiết bài viết
            // ==========================================
            case 'like_post':
            case 'comment':
            case 'mention':
                // 1. Chọc xuống DB check coi bài viết còn sống không
                $post = \App\Models\Post::find($notification->reference_id);

                // 2. Nếu bài viết bay màu rồi
                if (!$post) {
                    // Tiện tay xóa luôn cái thông báo vô dụng này dưới DB
                    $notification->delete();

                    // Nhả false về để con JS bung Toast báo lỗi và xóa UI
                    return response()->json([
                        'success' => false,
                        'message' => 'Bài viết này không còn tồn tại hoặc đã bị xóa!'
                    ]);
                }

                // 3. Nếu bài viết còn sống nhăn răng thì mới tạo link
                $redirectUrl = route('posts.show', $post->id);
                break;

            // ==========================================
            // NHÓM MỐI QUAN HỆ: Nhảy tới trang cá nhân (Profile)
            // ==========================================
            case 'follow':
            case 'friend_request':
                // reference_id lúc này đang lưu ID của thằng đi follow/gửi kết bạn
                $actorUser = \App\Models\User::find($notification->reference_id);

                if ($actorUser) {
                    $redirectUrl = url('/profile/' . $actorUser->username);
                } else {
                    // Nếu User bị ban hoặc xóa acc thì cũng y chang bài viết
                    $notification->delete();
                    return response()->json([
                        'success' => false,
                        'message' => 'Người dùng này không còn tồn tại!'
                    ]);
                }
                break;

            default:
                $redirectUrl = url('/');
        }

        // Trả link về cho Frontend nó bẻ lái tự động
        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl
        ]);
    }

    /*
    Đánh dấu chưa đọc cho thông báo
    */
    public function markAsUnread($id)
    {
        $notification = \App\Models\Notification::find($id);

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Thông báo không tồn tại!']);
        }

        if ($notification->user_id == auth()->id()) {
            $notification->update(['is_read' => 0]);
            return response()->json(['success' => true, 'message' => 'Đã đánh dấu chưa đọc!']);
        }

        return response()->json(['success' => false, 'message' => 'Lỗi quyền truy cập!']);
    }

    /*
    Xóa một thông báo cụ thể
    */
    public function destroySingle($id)
    {
        $notification = \App\Models\Notification::find($id);

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Thông báo này không tồn tại!']);
        }

        if ($notification->user_id == auth()->id()) {
            $notification->delete();
            return response()->json(['success' => true, 'message' => 'Xóa thông báo thành công!']);
        }

        return response()->json(['success' => false, 'message' => 'Lỗi quyền truy cập!']);
    }
}