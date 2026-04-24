<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\PostMedia;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Lấy bài viết kèm theo user và media
        $posts = \App\Models\Post::with(['user', 'media'])->latest()->get();

        // Sửa chỗ này: trỏ vào thư mục social thay vì posts
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
    public function store(\Illuminate\Http\Request $request)
    {
        // 1. Kiểm tra dữ liệu
        $request->validate([
            'content' => 'required',
            'image' => 'nullable|image|max:2048'
        ]);

        // 2. Tạo bài đăng (Tạm thời fix cứng user_id = 1 nếu bạn chưa làm login)
        $post = \App\Models\Post::create([
            'user_id' => 1,
            'content' => $request->content,
            'privacy' => 0,
            'like_count' => 0,
            'comment_count' => 0
        ]);

        // 3. Xử lý file ảnh
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            // Lưu vào thư mục public/uploads/posts
            $file->move(public_path('uploads/posts'), $fileName);
            $url = asset('uploads/posts/' . $fileName);

            \App\Models\PostMedia::create([
                'post_id' => $post->id,
                'media_url' => $url,
                'media_type' => 'photo'
            ]);
        }

        return redirect()->back()->with('success', 'Bài viết đã được đăng!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id) {}
    /**
     * Show the form for editing the specified resource.
     */
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

        // 1. Validate dữ liệu
        $request->validate([
            'content' => 'required|string',
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // 2. Cập nhật nội dung chữ
        $post->content = $request->content;

        // 3. Xử lý ảnh (nếu người dùng chọn ảnh mới)
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu cần (tùy logic của bạn)
            // if ($post->image_url && file_exists(public_path($post->image_url))) { ... }

            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/posts'), $imageName);

            // Lưu đường dẫn vào database
            $post->image_url = 'uploads/posts/' . $imageName;
        }

        $post->save();

        return redirect()->back()->with('success', 'Cập nhật bài viết thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = \App\Models\Post::findOrFail($id);

        // 1. Xóa các bản ghi liên quan trong bảng post_media trước (để tránh lỗi khóa ngoại)
        // Nếu bạn có file ảnh thật trong thư mục public, bạn cũng nên xóa nó đi ở đây.
        $post->media()->delete();

        // 2. Xóa bài viết
        $post->delete();

        return redirect()->back()->with('success', 'Đã xóa bài viết thành công!');
    }
}
