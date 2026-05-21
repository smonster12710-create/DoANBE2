<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{
    // 1. Gửi lời mời kết bạn (Người A bấm)
    public function sendFriendRequest($username)
    {
        $me = Auth::user();
        $target = User::where('username', $username)->firstOrFail();

        // Không cho tự gửi lời mời cho chính mình
        if ($me->id === $target->id) {
            return back()->with('error', 'Không thể tự kết bạn với chính mình.');
        }

        // Nếu chưa có mối quan hệ gì thì mới cho gửi
        if ($me->getFriendshipStatus($target->id) === 'none') {
            $me->sentFriendRequests()->attach($target->id, ['status' => 'pending']);
            return back()->with('success', 'Đã gửi lời mời kết bạn đến ' . $target->fullname);
        }

        return back();
    }

    // 2. Chấp nhận kết bạn (Người B bấm)
    public function acceptFriendRequest($username)
    {
        $me = Auth::user();
        $target = User::where('username', $username)->firstOrFail();

        // Chuyển trạng thái từ pending sang accepted
        $me->receivedFriendRequests()->updateExistingPivot($target->id, ['status' => 'accepted']);

        return back()->with('success', 'Bạn và ' . $target->fullname . ' đã trở thành bạn bè!');
    }

    // 3. Hủy kết bạn / Từ chối lời mời / Hủy lời mời đã gửi
    public function removeFriend($username)
    {
        $me = Auth::user();
        $target = User::where('username', $username)->firstOrFail();

        // Xóa sạch mọi liên kết giữa 2 người trong bảng friendships
        $me->sentFriendRequests()->detach($target->id);
        $me->receivedFriendRequests()->detach($target->id);

        return back()->with('success', 'Thao tác thành công.');
    }
    
    public function friends($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $friendIds = DB::table('friendships')
            ->where('user_id', $user->id)
            ->pluck('friend_id')
            ->merge(
                DB::table('friendships')
                    ->where('friend_id', $user->id)
                    ->pluck('user_id')
            );

        $users = User::whereIn('id', $friendIds)->get();

        $title = 'Bạn bè của bạn';

        return view('social.profile_list', compact('user', 'users', 'title'));
    }
}
