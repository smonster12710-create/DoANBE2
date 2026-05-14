<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\TextProcessorService;
use App\Models\Notification;
use App\Models\User;


class CommentController extends Controller
{
    public function store(Request $request, $postId, TextProcessorService $textProcessorService)
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

        $mentionedUsernames = $textProcessorService->getMentions($comment->content);

        if (!empty($mentionedUsernames)) {
            // 3. Chọt DB, gom cổ mấy ông nội có tên trong mảng này lên
            // Lưu ý: Cột 'username' phải khớp với tên cột tài khoản trong DB của Pro nha
            $mentionedUsers = User::whereIn('username', $mentionedUsernames)->get();

            // 4. Bơm thông báo cho từng người
            foreach ($mentionedUsers as $user) {
                // Luật giang hồ: Không tự bắn thông báo cho chính mình (lỡ tay gõ @ten_minh)
                if ($user->id !== Auth::id()) {
                    Notification::create([
                        'user_id' => $user->id,          // Người bị réo tên (nhận thông báo)
                        'actor_id' => Auth::id(),        // Người gõ phím tag (chính là mình)
                        'type' => 'mention',             // Loại thông báo: mention
                        'reference_id' => $comment->post_id, // Gắn ID bài viết vô để mốt click thông báo nó bay tới đây
                        'is_read' => 0,                  // Chưa đọc
                    ]);
                }
            }
        }

        return back()->with('success', 'Đã thêm bình luận!');
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        // Lấy ID người dùng hiện tại
        $currentUserId = Auth::id();

        // Kiểm tra quyền
        if ($currentUserId == $comment->user_id || $currentUserId == $comment->post->user_id) {
            $comment->delete();
            return back()->with('success', 'Đã xóa bình luận!');
        }

        return back()->with('error', 'Bạn không có quyền xóa bình luận này.');
    }
}
