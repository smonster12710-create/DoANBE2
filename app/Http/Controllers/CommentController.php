<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, $postId)
    {
        // 1. Kiểm tra đăng nhập ngay tại đây cho chắc
        if (!Auth::check()) {
            return back()->with('error', 'Bạn phải đăng nhập để bình luận!');
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = new Comment();
        $comment->post_id = $postId;
        $comment->user_id = Auth::id(); // Lúc này Auth::id() chắc chắn có giá trị
        $comment->content = $request->content;
        $comment->save();

        return back()->with('success', 'Đã thêm bình luận!');
    }
}
