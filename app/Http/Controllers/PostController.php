<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function toggleLike($postId)
    {
        // 1. Xác định User
        $userId = \Illuminate\Support\Facades\Auth::id() ?? 1;

        // 2. Tìm bài viết
        $post = Post::findOrFail($postId);

        // 3. Kiểm tra xem user này đã like bài này chưa
        $like = Like::where('post_id', $postId)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            // Xóa trực tiếp bằng Query Builder để tránh lỗi Primary Key
            Like::where('post_id', $postId)
                ->where('user_id', $userId)
                ->delete();

            // Chỉ giảm số lượng nếu like_count đang lớn hơn 0
            if ($post->like_count > 0) {
                $post->decrement('like_count');
            }
        } else {
            // Thêm mới lượt like
            Like::create([
                'post_id' => $postId,
                'user_id' => $userId
            ]);

            // Tăng số lượng like
            $post->increment('like_count');
        }

        return redirect()->back();
    }

    public function listLikers($postId)
    {
        // Lấy bài viết và load danh sách người dùng đã like
        $post = Post::with('likedByUsers')->findOrFail($postId);

        return view('social.post_likers', compact('post'));
    }

    public function index()
    {
        // Lấy dữ liệu thật từ DB
        $posts = Post::with(['user', 'likes'])->latest()->get();

        // Trả về file social_home.blade.php
        return view('social.index', compact('posts'));
    }
}
