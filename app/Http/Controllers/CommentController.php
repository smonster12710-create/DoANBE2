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

        // ====================================================================
        //  BẮN THÔNG BÁO 
        // ====================================================================
        $post = Post::find($postId);
        // Luật giang hồ: Không tự báo cho mình nếu tự comment bài mình
        if ($post && $post->user_id !== Auth::id()) {
            Notification::create([
                'user_id' => $post->user_id,      // Người nhận là chủ bài viết
                'actor_id' => Auth::id(),         // Người comment (chính là mình)
                'type' => 'comment',              // Loại thông báo: comment
                'reference_id' => $postId,        // Gắn ID bài viết để mốt click vô nhảy cho lẹ
                'is_read' => 0,                   // Chưa đọc
            ]);
        }
        // ====================================================================

        // LOGIC XỬ LÝ MENTION 
        $mentionedUsernames = $textProcessorService->getMentions($comment->content);

        if (!empty($mentionedUsernames)) {
            // vào db lấy user có tên trong mảng này lên
            $mentionedUsers = User::whereIn('username', $mentionedUsernames)->get();

            //  thông báo cho từng người bị tag
            foreach ($mentionedUsers as $user) {
                // Không tự bắn thông báo cho chính mình (lỡ tay gõ @ten_minh)
                if ($user->id !== Auth::id()) {
                    Notification::create([
                        'user_id' => $user->id,
                        'actor_id' => Auth::id(),
                        'type' => 'mention',
                        'reference_id' => $comment->post_id,
                        'is_read' => 0,
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
