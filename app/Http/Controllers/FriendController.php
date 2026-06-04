<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{
    /**
     * 1. GỬI LỜI MỜI KẾT BẠN (Người A chủ động bấm nút "Thêm bạn bè")
     */
    public function sendFriendRequest(Request $request, $username)
    {
        $me = Auth::user(); // Lấy thông tin người gửi (Người A)
        $target = User::where('username', $username)->firstOrFail(); // Tìm thông tin người nhận (Người B)

        // 1. Kiểm tra sự đồng bộ
        if ($me->getFriendshipStatus($target->id) !== $request->input('expected_status')) {
            return back()->with('error', 'Trạng thái đã thay đổi, trang sẽ tải lại để cập nhật.');
        }

        // Không cho phép tự kết bạn với chính mình
        if ($me->id === $target->id) {
            return back()->with('error', 'Không thể tự kết bạn với chính mình.');
        }

        // Chỉ gửi lời mời nếu giữa 2 người chưa có mối quan hệ ('none')
        if ($me->getFriendshipStatus($target->id) === 'none') {

            // Bước A: Thêm mối quan hệ chờ duyệt vào bảng liên kết `friendships`
            $me->sentFriendRequests()->attach($target->id, ['status' => 'pending']);

            // Bước B: ✨ ĐÃ FIX KHỚP 100% CẤU TRÚC: Lưu thông báo vào bảng notifications của bạn
            DB::table('notifications')->insert([
                'user_id'      => $target->id,      // ID người nhận thông báo (Người B)
                'actor_id'     => $me->id,          // ID người thực hiện/người gửi (Người A)
                'type'         => 'friend_request', // Đặt tên loại thông báo là kết bạn
                'reference_id' => $me->id,          // Dùng làm ID tham chiếu dọn dẹp sau này
                'is_read'      => 0,                // Mặc định là chưa đọc (0)
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // Bước C: Bắn tín hiệu realtime qua Reverb cho đối phương nhận diện tức thì
            event(new \App\Events\FriendRequestSent($me, $target));

            return back()->with('success', 'Đã gửi lời mời kết bạn đến ' . $target->fullname);
        }

        return back();
    }

    /**
     * 2. CHẤP NHẬN KẾT BẠN (Người B bấm nút "Chấp nhận")
     */
    public function acceptFriendRequest(Request $request, $username)
    {
        $me = Auth::user();
        $target = User::where('username', $username)->firstOrFail();

        // LẤY TRẠNG THÁI THỰC TẾ
        $status = $me->getFriendshipStatus($target->id);

        // Ở đây, nếu bạn là người nhận, status sẽ là 'requested'
        // Chúng ta chỉ cho phép chấp nhận nếu status là 'requested'
        if ($status !== 'requested') {
            return back()->with('error', 'Lời mời không còn hiệu lực.');
        }

        // Cập nhật trạng thái
        $me->receivedFriendRequests()->updateExistingPivot($target->id, ['status' => 'accepted']);

        // Xóa notification
        DB::table('notifications')
            ->where('user_id', $me->id)
            ->where('actor_id', $target->id)
            ->where('type', 'friend_request')
            ->delete();

        return back()->with('success', 'Bạn và ' . $target->fullname . ' đã trở thành bạn bè!');
    }

    /**
     * 3. TỪ CHỐI LỜI MỜI / HỦY KẾT BẠN
     */
    public function removeFriend(Request $request, $username)
    {
        $me = Auth::user();
        $target = User::where('username', $username)->firstOrFail();

        $currentStatus = $me->getFriendshipStatus($target->id);

        // THÊM 'accepted' VÀO ĐÂY
        $allowedStatuses = ['pending', 'requested', 'accepted'];

        if (!in_array($currentStatus, $allowedStatuses)) {
            return back()->with('error', 'Thao tác không hợp lệ hoặc trạng thái đã thay đổi.');
        }

        // Bước A: Hủy bỏ hoàn toàn
        $me->sentFriendRequests()->detach($target->id);
        $me->receivedFriendRequests()->detach($target->id);

        // Bước B: Dọn dẹp thông báo (giữ nguyên)
        DB::table('notifications')
            ->where('type', 'friend_request')
            ->where(function ($query) use ($me, $target) {
                $query->where(function ($q) use ($me, $target) {
                    $q->where('user_id', $me->id)->where('actor_id', $target->id);
                })->orWhere(function ($q) use ($me, $target) {
                    $q->where('user_id', $target->id)->where('actor_id', $me->id);
                });
            })->delete();

        return back()->with('success', 'Thao tác thành công.');
    }

    /**
     * 4. TRANG DANH SÁCH LỜI MỜI KẾT BẠN ĐÃ NHẬN
     */
    public function friendRequests()
    {
        $me = Auth::user();
        $users = $me->receivedFriendRequests()->wherePivot('status', 'pending')->get();
        $title = 'Lời mời kết bạn đã nhận';

        return view('social.friend_requests', compact('users', 'title'));
    }

    /**
     * 5. TRANG TỔNG HỢP DANH SÁCH BẠN BÈ
     */
    public function friends($username)
    {
        // 1. Thay firstOrFail() bằng first() để tự xử lý
        $user = User::where('username', $username)->first();

        // 2. Kiểm tra nếu không tìm thấy user
        if (!$user) {
            return view('social.custom_message', [
                'message' => 'Người dùng này không tồn tại.'
            ]);
        }

        $me = auth()->user();

        // 3. Thực hiện truy vấn danh sách bạn bè
        $friendIds = DB::table('friendships')
            ->where('user_id', $user->id)
            ->where('status', 'accepted')
            ->pluck('friend_id')
            ->merge(
                DB::table('friendships')
                    ->where('friend_id', $user->id)
                    ->where('status', 'accepted')
                    ->pluck('user_id')
            );

        $friends = User::whereIn('id', $friendIds)->get();
        $friendRequests = collect();

        if ($me && $me->id === $user->id) {
            $friendRequests = $me->receivedFriendRequests()->wherePivot('status', 'pending')->get();
        }

        $title = 'Bạn bè của ' . ($user->fullname ?? 'Người dùng');

        return view('social.profile_list', compact('user', 'friends', 'friendRequests', 'title'));
    }
}
