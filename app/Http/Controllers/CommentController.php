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
        // Tìm bài viết để lấy thông tin (nhất là ID chủ bài viết)
        $post = Post::find($postId);
        // 1. Kiểm tra đăng nhập
        if (!Auth::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Bạn phải đăng nhập để bình luận!'], 401);
            }
            return back()->with('error', 'Bạn phải đăng nhập để bình luận!');
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = new Comment();
        $comment->post_id = $postId;
        $comment->user_id = Auth::id();
        $comment->content = $request->content;
        $comment->save();

        // ====================================================================
        event(new \App\Events\PostComment(Auth::user(), $post, $comment));
        // ====================================================================
        // LOGIC XỬ LÝ MENTION 
        $mentionedUsernames = $textProcessorService->getMentions($comment->content);
        if (!empty($mentionedUsernames)) {
            event(new \App\Events\PostMention(Auth::user(), $post, $mentionedUsernames));
        }
        // ====================================================================

        if ($request->ajax() || $request->wantsJson()) {
            // ĐÃ CẬP NHẬT: Ưu tiên lấy trang cá nhân/fanpage hiện tại trong session nếu có
            $currentProfile = session('current_profile') ?? Auth::user();

            // Xác định tên hiển thị chính xác của trang cá nhân
            $fullName = $currentProfile->fullname ?? $currentProfile->name ?? Auth::user()->fullname ?? 'Người dùng';

            // Xác định đúng avatar của trang cá nhân đang chọn
            if (!empty($currentProfile->avatar_url)) {
                $avatarUrl = asset($currentProfile->avatar_url);
            } elseif (!empty(Auth::user()->avatar_url)) {
                $avatarUrl = asset(Auth::user()->avatar_url);
            } else {
                $avatarUrl = 'https://i.pravatar.cc/40?u=' . Auth::id();
            }

            return response()->json([
                'success' => true,
                'user_fullname' => $fullName,
                'user_avatar' => $avatarUrl,
                'comment_id' => $comment->id,
                'created_at' => 'Vừa xong',
                'destroy_route' => route('comments.destroy', $comment->id) // Trả về link xóa để JS vẽ nút xóa
            ]);
        }

        return back()->with('success', 'Đã thêm bình luận!');
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        // Lấy ID người dùng hiện tại
        $currentUserId = Auth::id();

        // Kiểm tra quyền (Chủ comment hoặc Chủ bài viết)
        if ($currentUserId == $comment->user_id || $currentUserId == $comment->post->user_id) {
            $comment->delete();

            // NẾU LÀ AJAX: Trả về JSON thành công để JS xóa block comment trên màn hình
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã xóa bình luận!'
                ]);
            }

            // Nếu không phải AJAX (form bình thường) thì quay lại trang cũ
            return back()->with('success', 'Đã xóa bình luận!');
        }

        // NẾU LÀ AJAX NHƯNG THẤT BẠI (Không có quyền xóa)
        if (request()->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa bình luận này.'
            ], 403); // Trả về mã lỗi 403 Forbidden
        }

        // Nếu không phải AJAX và thất bại
        return back()->with('error', 'Bạn không có quyền xóa bình luận này.');
    }

    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        // 1. Kiểm tra tính toàn vẹn dữ liệu (tránh sửa trùng)
        $requestTimestamp = strtotime($request->input('last_updated_at'));
        $dbTimestamp = strtotime($comment->updated_at);

        if ($requestTimestamp !== $dbTimestamp) {
            return response()->json([
                'success' => false,
                'message' => 'Bình luận đã bị thay đổi, trang sẽ tải lại.',
                'reload' => true
            ], 200);
        }

        // 2. Validate dữ liệu với giới hạn 1000 ký tự
        $request->validate([
            'content' => 'required|string|max:1000',
        ], [
            'content.max' => 'Bình luận không được vượt quá 1000 ký tự.',
            'content.required' => 'Nội dung bình luận không được để trống.',
        ]);

        // 3. Lưu bình luận
        $comment->content = $request->input('content');
        $comment->save();

        return response()->json([
            'success' => true,
            'content' => $comment->content,
            'updated_at' => $comment->updated_at->toDateTimeString()
        ]);
    }
}
