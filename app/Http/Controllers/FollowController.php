<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FollowController extends Controller
{
    public function toggle(Request $request, $userId)
    {
        $me = Auth::user();
        $targetUser = User::findOrFail($userId);

        if ($me->id == $targetUser->id) {
            return back()->with('error', 'Không thể tự theo dõi chính mình');
        }

        // Kiểm tra thực tế trong DB hiện tại
        $actualFollowing = $me->followings()->where('following_id', $userId)->exists();

        // Kiểm tra trạng thái mà form gửi lên (expected_status)
        $expectedStatus = filter_var($request->input('expected_status'), FILTER_VALIDATE_BOOLEAN);

        // NẾU TRẠNG THÁI KHÔNG KHỚP -> Tab đã bị cũ
        if ($actualFollowing !== $expectedStatus) {
            return back()->with('error', 'Trạng thái theo dõi đã thay đổi ở tab khác, trang đã được cập nhật.');
        }

        // Nếu khớp, xử lý bình thường
        if ($actualFollowing) {
            $me->followings()->detach($userId);
            return back()->with('success', 'Đã bỏ theo dõi ' . $targetUser->fullname);
        } else {
            $me->followings()->attach($userId);
            return back()->with('success', 'Đã theo dõi thành công ' . $targetUser->fullname);
        }
    }

    public function following($username)
    {
        // Bỏ firstOrFail() và dùng tìm kiếm thông thường
        $user = User::where('username', $username)->first();

        // Kiểm tra nếu không có user
        if (!$user) {
            // TRẢ VỀ VIEW THÔNG BÁO
            return view('social.custom_message', [
                'message' => 'Người dùng này không tồn tại.'
            ]);
        }

        $ids = DB::table('follows')
            ->where('follower_id', $user->id)
            ->pluck('following_id');

        $users = User::whereIn('id', $ids)->get();

        $title = 'Đang theo dõi';
        return view('social.profile_list', compact('user', 'users', 'title'));
    }
}
