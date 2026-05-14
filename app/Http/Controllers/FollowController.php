<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function toggle($userId) // Nhận ID trực tiếp để tránh lỗi Route Binding
{
    $me = Auth::user();
    $targetUser = User::findOrFail($userId);

    if ($me->id == $targetUser->id) {
        return back()->with('error', 'Không thể tự theo dõi chính mình');
    }

    // Kiểm tra thủ công xem đã follow chưa
    $isFollowing = $me->followings()->where('following_id', $userId)->exists();

    if ($isFollowing) {
        // Nếu đang follow rồi thì XÓA (Bỏ theo dõi)
        $me->followings()->detach($userId);
        return back()->with('success', 'Đã bỏ theo dõi ' . $targetUser->fullname);
    } else {
        // Nếu chưa follow thì THÊM (Theo dõi)
        $me->followings()->attach($userId);
        return back()->with('success', 'Đã theo dõi thành công ' . $targetUser->fullname);
    }
}

    public function followingList()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Lấy danh sách người được follow, phân trang 12 người mỗi trang
        $followings = $user->followings()->paginate(12);

        return view('social.followings', compact('followings'));
    }
}
