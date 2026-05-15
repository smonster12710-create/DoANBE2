<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Hiển thị trang cá nhân (Cho cả mình và người khác)
     */
    public function show($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $posts = \App\Models\Post::with(['user', 'media', 'likes', 'comments'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $authId = auth()->id();

        $friendsCount = DB::table('friendships')
            ->where('user_id', $user->id)
            ->orWhere('friend_id', $user->id)
            ->count();

        $postsCount = $posts->count();

        $followersCount = DB::table('follows')
            ->where('following_id', $user->id)
            ->count();

        $followingCount = DB::table('follows')
            ->where('follower_id', $user->id)
            ->count();

        $isFriend = DB::table('friendships')
            ->where(function ($q) use ($authId, $user) {
                $q->where('user_id', $authId)
                    ->where('friend_id', $user->id);
            })
            ->orWhere(function ($q) use ($authId, $user) {
                $q->where('user_id', $user->id)
                    ->where('friend_id', $authId);
            })
            ->exists();

        $isFollowing = DB::table('follows')
            ->where('follower_id', $authId)
            ->where('following_id', $user->id)
            ->exists();

        return view('social.profile', compact(
            'user',
            'posts',
            'friendsCount',
            'postsCount',
            'followersCount',
            'followingCount',
            'isFriend',
            'isFollowing'
        ));
    }
    public function toggleFriend($username)
    {
        $targetUser = User::where('username', $username)->firstOrFail();

        if (auth()->id() == $targetUser->id) {
            return back();
        }

        $authId = auth()->id();

        $friendship = DB::table('friendships')
            ->where(function ($q) use ($authId, $targetUser) {
                $q->where('user_id', $authId)
                    ->where('friend_id', $targetUser->id);
            })
            ->orWhere(function ($q) use ($authId, $targetUser) {
                $q->where('user_id', $targetUser->id)
                    ->where('friend_id', $authId);
            })
            ->first();

        if ($friendship) {
            DB::table('friendships')->where('id', $friendship->id)->delete();
        } else {
            DB::table('friendships')->insert([
                'user_id' => $authId,
                'friend_id' => $targetUser->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back();
    }

    // public function toggleFollow($username)
    // {
    //     $targetUser = User::where('username', $username)->firstOrFail();

    //     if (auth()->id() == $targetUser->id) {
    //         return back();
    //     }

    //     $authId = auth()->id();

    //     $follow = DB::table('follows')
    //         ->where('follower_id', $authId)
    //         ->where('following_id', $targetUser->id)
    //         ->first();

    //     if ($follow) {
    //         DB::table('follows')->where('id', $follow->id)->delete();
    //     } else {
    //         DB::table('follows')->insert([
    //             'follower_id' => $authId,
    //             'following_id' => $targetUser->id,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);
    //     }

    //     return back();
    // }
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

    public function followers($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $ids = DB::table('follows')
            ->where('following_id', $user->id)
            ->pluck('follower_id');

        $users = User::whereIn('id', $ids)->get();

        $title = 'Người theo dõi của bạn';
        return view('social.profile_list', compact('user', 'users', 'title'));
    }

    
    /**
     * Hiển thị trang chỉnh sửa (Chỉ cho chính mình)
     */
    public function edit()
    {
        $user = Auth::user();
        return view('social.edit-profile', compact('user'));
    }

    /**
     * Xử lý cập nhật thông tin và upload ảnh
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cập nhật thông tin cơ bản
        $user->fill($request->only([
            'fullname',
            'username',
            'email',
            'bio',
            'birthday',
            'phone',
            'gender',
            'address'
        ]));

        // Xử lý Upload Avatar
        if ($request->hasFile('avatar')) {
            $avatarName = time() . '_avatar.' . $request->avatar->extension();
            $request->avatar->move(public_path('uploads_profile/avatar'), $avatarName);
            $user->avatar_url = 'uploads_profile/avatar/' . $avatarName;
        }

        // Xử lý Upload Cover
        if ($request->hasFile('cover')) {
            $coverName = time() . '_cover.' . $request->cover->extension();
            $request->cover->move(public_path('uploads_profile/cover'), $coverName);
            $user->cover_url = 'uploads_profile/cover/' . $coverName;
        }

        $user->save();

        // Quay lại trang cá nhân của chính mình sau khi update
        return redirect()->route('profile.show', ['username' => $user->username])
            ->with('success', 'Đã cập nhật profile thành công!');
    }
}
