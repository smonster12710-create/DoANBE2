<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostMedia;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\TextProcessorService;

class PostController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Lấy bài viết kèm theo user, media và cả likes (để hiển thị số lượt like)
        $posts = Post::with(['user', 'media', 'likes'])
            ->orderByDesc('is_pinned') // 🔥 ghim lên trước
            ->latest()
            ->get();

        // Trỏ vào view index trong thư mục social
        return view('social.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, TextProcessorService $hashtagService)
    {
        // 1. Cập nhật validate, thêm nullable cho expires_in
        $request->validate([
            'content' => 'required|string',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,jfif,webp|max:2048',
            'expires_in' => 'nullable|integer',
        ]);

        // 2. Tính toán thời gian hết hạn nếu người dùng có chọn
        $expiresAt = null;
        if ($request->filled('expires_in')) {
            // Thêm (int) vào trước để ép kiểu dữ liệu chuỗi "1" thành số nguyên 1
            $expiresAt = now()->addMinutes((int) $request->expires_in);
        }

        // 3. Tạo bài viết
        $post = new Post();
        $post->user_id = Auth::id();
        $post->content = $request->content;
        $post->privacy = 0;
        $post->expires_at = $expiresAt; // Lưu thời gian hết hạn vào DB
        $post->save();

        // ===========================================================================
        // 4. Xử lý hashtag
        $tagNames = $hashtagService->getHashtags($post->content);
        $tagIds = [];

        if (!empty($tagNames)) {
            foreach ($tagNames as $tagName) {
                $hashtag = \App\Models\Hashtag::firstOrCreate([
                    'name' => mb_strtolower($tagName, 'UTF-8')
                ]);
                // Tăng count lên 1 nhịp
                $hashtag->increment('usage_count');
                // Nhét cái ID (số nguyên) vô mảng
                $tagIds[] = $hashtag->id;
            }
        }

        // Đồng bộ ID hashtag vô bảng trung gian post_hashtags 
        // (Mình đã xóa 1 đoạn code bị trùng lặp của bạn ở đây để code chạy tối ưu hơn)
        if (!empty($tagIds)) {
            $post->hashtags()->sync($tagIds);
        }

        // 5. Nếu có ảnh thì lưu vào post_media
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/posts'), $filename);

                $media = new PostMedia();
                $media->post_id = $post->id;
                $media->media_url = 'uploads/posts/' . $filename;
                $media->media_type = 'photo';
                $media->save();
            }
        }

        return back()->with('success', 'Đăng bài thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $post = Post::with(['user', 'media', 'likes'])->findOrFail($id);
        return view('social.show', compact('post'));
    }
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    // Nhớ import ở đầu file

    public function update(Request $request, $id, \App\Services\TextProcessorService $hashtagService)
    {
        $post = Post::findOrFail($id);

        // CHỈ CHỦ BÀI VIẾT MỚI ĐƯỢC SỬA
        if ($post->user_id != Auth::id()) {
            abort(403, 'Bạn không có quyền sửa bài viết này.');
        }

        $request->validate([
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        // Cập nhật nội dung chữ trước
        $post->content = $request->content;
        $post->save();

        // =========================================================
        // LOGIC LỌC VÀ CẬP NHẬT HASHTAG 
        // =========================================================
        $tagNames = $hashtagService->getHashtags($post->content);
        $tagIds = [];

        if (!empty($tagNames)) {
            foreach ($tagNames as $tagName) {
                $hashtag = \App\Models\Hashtag::firstOrCreate([
                    'name' => mb_strtolower($tagName, 'UTF-8')
                ]);

                // Tăng count lên 1 
                $hashtag->increment('usage_count');

                // Đưa ID vô mảng
                $tagIds[] = $hashtag->id;
            }
        }
        $post->hashtags()->sync($tagIds);
        // =========================================================

        // Xử lý hình ảnh 
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/posts'), $fileName);

            // xóa ảnh cũ
            if ($post->media->count()) {
                $post->media()->delete();
            }

            $post->media()->create([
                'media_url' => 'uploads/posts/' . $fileName,
                'media_type' => 'image',
            ]);
        }

        return redirect()->back()->with('success', 'Cập nhật bài viết thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        // CHỈ CHỦ BÀI VIẾT MỚI ĐƯỢC XÓA
        if ($post->user_id != Auth::id()) {
            abort(403, 'Bạn không có quyền xóa bài viết này.');
        }

        // xóa media
        $post->media()->delete();

        // xóa bài viết
        $post->delete();

        return redirect()->back()->with('success', 'Đã xóa bài viết thành công!');
    }
    public function toggleLike($postId)
    {
        $userId = Auth::id();
        if (!$userId)
            return response()->json(['error' => 'Unauthenticated'], 401);

        // Tìm xem đã like chưa
        $likeQuery = \App\Models\Like::where('post_id', $postId)->where('user_id', $userId);

        if ($likeQuery->exists()) {
            // Trường hợp BỎ LIKE: Xóa like cũ
            $likeQuery->delete();
            $isLiked = false;
        } else {
            // Trường hợp LIKE MỚI: Tạo record
            \App\Models\Like::create([
                'post_id' => $postId,
                'user_id' => $userId
            ]);
            $isLiked = true;

            // ====================================================================
            // LOGIC BẮN THÔNG BÁO
            // ====================================================================
            $post = \App\Models\Post::find($postId);

            // Check bài viết có tồn tại không
            if ($post && $post->user_id !== $userId) {
                \App\Models\Notification::create([
                    'user_id' => $post->user_id,      // User nhận thông báo (chủ bài viết)
                    'actor_id' => $userId,            // User thả tim (chính là mình)
                    'type' => 'like',                 // Phân loại là 'like' để mốt Frontend biết đường hiện icon cho chuẩn
                    'reference_id' => $postId,        // Lưu ID bài để mốt click vô nó đá qua đúng bài
                    'is_read' => 0,                   // Đánh dấu chưa đọc
                ]);
            }
            // ====================================================================
        }

        // Đếm lại tổng số tim
        $count = \App\Models\Like::where('post_id', $postId)->count();

        // Nhả cục JSON về cho con Frontend nó render
        return response()->json([
            'isLiked' => $isLiked,
            'likeCount' => (int) $count
        ]);
    }

    public function listLikers($postId)
    {
        // Lấy bài viết và load danh sách người dùng đã like
        $post = Post::with('likedByUsers')->findOrFail($postId);

        return view('social.post_likers', compact('post'));
    }
    public function togglePin($id)
    {
        $post = Post::findOrFail($id);

        // CHỈ CHỦ BÀI VIẾT MỚI ĐƯỢC GHIM
        if ($post->user_id != Auth::id()) {
            abort(403, 'Bạn không có quyền ghim bài viết này.');
        }

        $post->is_pinned = !$post->is_pinned;
        $post->save();

        return back();
    }
    public function save(Post $post)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Hàm toggle() của Laravel trả về một mảng chứa thông tin:
        // 'attached' => các ID vừa được thêm vào (Lưu)
        // 'detached' => các ID vừa được xóa đi (Hủy lưu)
        $result = $user->savedPosts()->toggle($post->id);

        // Kiểm tra xem bài viết vừa được lưu hay hủy lưu
        $isSaved = count($result['attached']) > 0;

        // Trả về JSON cho JavaScript xử lý ngầm
        return response()->json([
            'success' => true,
            'is_saved' => $isSaved, // true nếu là vừa Lưu, false nếu là vừa Hủy lưu
        ]);
    }
    public function saved()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $posts = $user->savedPosts()
            ->latest()
            ->get();

        return view('posts.saved', compact('posts'));
    }
    public function searchPosts(Request $request)
    {
        $q = $request->q;

        $posts = Post::with('user')
            ->where('content', 'LIKE', "%{$q}%")
            ->latest()
            ->paginate(5);

        return response()->json([
            'data' => $posts
        ]);
    }
    public function share(Request $request, $id)
    {
        // Xác thực bài viết gốc xem có tồn tại không
        $originalPost = Post::findOrFail($id);

        // Tạo bản ghi bài viết mới hoàn toàn
        $sharedPost = new Post();
        $sharedPost->user_id = Auth::id(); // Người share bài
        $sharedPost->parent_id = $originalPost->id; // Gắn ID bài gốc vào đây!
        $sharedPost->content = $request->input('content'); // Lời bình luận khi share
        $sharedPost->privacy = 0; // Công khai mặc định
        $sharedPost->save();

        return redirect()->back()->with('success', 'Đã chia sẻ bài viết thành công lên Profile!');
    }
    public function toggleComment($id)
    {
        $post = Post::findOrFail($id);

        if (Auth::id() !== $post->user_id) {
            return redirect()->back();
        }

        $secretKey = ' [#LOCK_COMMENT#]';

        // Nếu đang chặn (có chữ bí mật) -> Xóa chữ đó đi để MỞ lại
        if (\Illuminate\Support\Str::contains($post->content, $secretKey)) {
            $post->content = str_replace($secretKey, '', $post->content);
            $message = 'Đã mở lại bình luận.';
        } else {
            // Nếu chưa chặn -> Cộng thêm chữ bí mật vào cuối để CHẶN
            $post->content = $post->content . $secretKey;
            $message = 'Đã chặn bình luận thành công.';
        }

        $post->save();

        return redirect()->back()->with('success', $message);
    }
}
