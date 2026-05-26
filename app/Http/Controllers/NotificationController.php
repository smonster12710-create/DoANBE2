<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = \App\Models\Notification::with('actor')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('social.notifications', compact('notifications'));
    }

    public function getMyNotifications()
    {
        $userId = auth()->id();

        $notifications = \App\Models\Notification::with('actor')
            ->where('user_id', $userId) // Sửa with thành where
            ->latest()
            ->take(5)
            ->get();

        // Tui sửa lại tên biến cho đúng chuẩn unreadCount (không có chữ e) cho đẹp
        $unreadCount = \App\Models\Notification::where('user_id', $userId)
            ->where('is_read', 0)
            ->count();

        // Sửa lại cú pháp compact
        return view('social.notifications', compact('notifications', 'unreadCount'));
    }

    public function markAsRead()
    {
        \App\Models\Notification::where('user_id', auth()->id())
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return response()->json(['status' => 'success']);
    }

    public function readSingle($id)
    {
        $notification = \App\Models\Notification::find($id);

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Thông báo không tồn tại!']);
        }

        // 1. KIỂM TRA BÀI POST (Chỉ check nếu thông báo này thuộc về bài viết)
        if (in_array($notification->type, ['like_post', 'comment', 'mention'])) {
            $post = \App\Models\Post::find($notification->reference_id);

            // Nếu bài viết hổng còn tồn tại
            if (!$post) {
                // Dọn rác: Xóa luôn thông báo khỏi Database
                $notification->delete();

                // Báo về cho Frontend biết để bung Toast
                return response()->json([
                    'success' => false,
                    'message' => 'Bài viết này không tồn tại hoặc đã bị xóa!'
                ]);
            }
        }

        // 2.  Đánh dấu đã đọc
        if ($notification->user_id == auth()->id() && $notification->is_read == 0) {
            $notification->update(['is_read' => 1]);
        }

        // 3. Trả về link chuyển trang
        return response()->json([
            'success' => true,
            'redirect_url' => route('posts.show', $notification->reference_id)
        ]);
    }

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