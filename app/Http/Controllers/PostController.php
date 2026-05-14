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
        $request->validate([
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        // tạo bài viết trước
        $post = new Post();
        $post->user_id = Auth::id();
        $post->content = $request->content;
        $post->privacy = 0;
        $post->save();
        // ===========================================================================
        //Xử lý hashtag
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
            $post->hashtags()->sync($tagIds);
        }

        // Đồng bộ ID hashtag vô bảng trung gian post_hashtags
        if (!empty($tagIds)) {
            $post->hashtags()->sync($tagIds);
        }
        // nếu có ảnh thì lưu vào post_media
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            $filename = time() . '_' . $file->getClientOriginalName();

            $file->move(public_path('uploads/posts'), $filename);

            $media = new PostMedia();
            $media->post_id = $post->id;
            $media->media_url = 'uploads/posts/' . $filename;
            $media->media_type = 'photo';
            $media->save();
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

    public function update(Request $request, $id)
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

        $post->content = $request->content;
        $post->save();

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
        if (!$userId) return response()->json(['error' => 'Unauthenticated'], 401);

        // Tìm xem đã like chưa
        $likeQuery = Like::where('post_id', $postId)->where('user_id', $userId);

        if ($likeQuery->exists()) {

            $likeQuery->delete();
            $isLiked = false;
        } else {
            Like::create([
                'post_id' => $postId,
                'user_id' => $userId
            ]);
            $isLiked = true;
        }

        $count = Like::where('post_id', $postId)->count();

        return response()->json([
            'isLiked' => $isLiked,
            'likeCount' => (int)$count
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

        $user->savedPosts()->toggle($post->id);

        return back();
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
}
