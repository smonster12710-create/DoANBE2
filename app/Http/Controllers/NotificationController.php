<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    public function index()
    {
        // Lấy toàn bộ thông báo của người này
        $notifications = \App\Models\Notification::with('actor')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        // Trỏ tới một file view mới tên là notifications.blade.php
        return view('partials.notifications', compact('notifications'));
    }
    public function getMyNotifications()
    {
        $userId = auth()->id();
        $notifications = \App\Models\Notification::with('actor')
            ->with('user_id', $userId) // Lấy thông tin người thực hiện hành động
            ->latest()
            ->take(5)
            ->get();
        $undereadCount = \App\Models\Notification::where('user_id', $userId)
            ->where('is_read', 0)
            ->count();
        return view('partials.notifications', compact('notifications,undereadCount'));
    }
    // Trong NotificationController
    public function markAsRead()
    {
        \App\Models\Notification::where('user_id', auth()->id())
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return response()->json(['status' => 'success']);
    }
    public function readSingle($id)
    {
        // 1. Tìm cái thông báo đó ra
        $notification = \App\Models\Notification::findOrFail($id);

        // 2. Chỉ cho phép đúng chủ nhân của thông báo mới được đổi trạng thái
        if ($notification->user_id == auth()->id()) {
            $notification->update(['is_read' => 1]); // Chuyển thành Đã đọc
        }

        // 3. Chuyển hướng người dùng tới bài viết (reference_id) 
        // Ví dụ route của Pro là /posts/123 thì viết vầy:
        return redirect()->route('posts.show', $notification->reference_id);
    }
}