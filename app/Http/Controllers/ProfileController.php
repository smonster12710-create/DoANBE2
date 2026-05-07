<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Hiển thị trang cá nhân (Cho cả mình và người khác)
     */
    public function show($username)
    {
        // 1. Tìm user theo username, không có thì văng 404
        $user = User::where('username', $username)->firstOrFail();

        // 2. Lấy danh sách bài viết của user này (Fix lỗi Undefined $posts)
        $posts = $user->posts()
            ->with(['user', 'media', 'likes', 'comments'])
            ->latest()
            ->get();

        return view('social.profile', compact('user', 'posts'));
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