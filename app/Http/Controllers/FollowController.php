<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FollowController extends Controller
{
    public function toggle($userId)
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
            event(new \App\Events\UserFollowed($me, $targetUser, false));
        return back()->with('success', 'Đã bỏ theo dõi ' . $targetUser->fullname);
    } else {
        // Nếu chưa follow thì THÊM (Theo dõi)
        $me->followings()->attach($userId);
            event(new \App\Events\UserFollowed($me, $targetUser, true));
        return back()->with('success', 'Đã theo dõi thành công ' . $targetUser->fullname);
    }
}

    public function following($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $ids = DB::table('follows')
            ->where('follower_id', $user->id)
            ->pluck('following_id');

        $users = User::whereIn('id', $ids)->get();

        $title = 'Đang theo dõi';
        return view('social.profile_list', compact('user', 'users', 'title'));
    }
}
