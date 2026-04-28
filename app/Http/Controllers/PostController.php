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
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,png,gif|max:2048',
        ]);

        // tạo bài viết trước
        $post = new Post();
        $post->user_id = Auth::id();
        $post->content = $request->content;
        $post->privacy = 0;
        $post->save();

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

        $request->validate([
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $post->content = $request->content;
        $post->save();

        // nếu có ảnh mới thì cập nhật ảnh
        if ($request->hasFile('image')) {

            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();

            $file->move(public_path('uploads/posts'), $fileName);

            // xóa ảnh cũ nếu có
            if ($post->media->count()) {
                $post->media()->delete();
            }

            // thêm ảnh mới
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
        $post = \App\Models\Post::findOrFail($id);

        // 1. Xóa các bản ghi liên quan trong bảng post_media trước (để tránh lỗi khóa ngoại)
        // Nếu bạn có file ảnh thật trong thư mục public, bạn cũng nên xóa nó đi ở đây.
        $post->media()->delete();

        // 2. Xóa bài viết
        $post->delete();

        return redirect()->back()->with('success', 'Đã xóa bài viết thành công!');
    }
}
