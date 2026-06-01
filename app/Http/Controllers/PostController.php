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
            ->whereNull('group_id') // 🔥 THÊM DÒNG NÀY: Chỉ lấy bài viết công cộng không thuộc nhóm nào
            // ->orderByDesc('is_pinned') // 🔥 XÓA HOẶC COMMENT DÒNG NÀY ĐI
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
        // Cần cho phép tối đa 10MB ở đây để máy nhận được file lớn trước khi đem đi nén
        $request->validate([
            'content' => 'required|string|max:550',
            'images.*' => 'nullable|image|max:10240',
            'expires_in' => 'nullable|integer',
        ]);

        $expiresAt = null;
        if ($request->filled('expires_in')) {
            $expiresAt = now()->addMinutes((int) $request->expires_in);
        }

        $post = new Post();
        $post->user_id = Auth::id();
        $post->content = $request->content;
        $post->privacy = 0;
        $post->expires_at = $expiresAt;
        // Thêm dòng này để lưu bài viết vào nhóm (nếu có, không có thì tự động là null)
        $post->group_id = $request->input('group_id', null);
        $post->save();

        // Xử lý hashtag
        $tagNames = $hashtagService->getHashtags($post->content);
        $tagIds = [];
        if (!empty($tagNames)) {
            foreach ($tagNames as $tagName) {
                $hashtag = \App\Models\Hashtag::firstOrCreate([
                    'name' => mb_strtolower($tagName, 'UTF-8')
                ]);
                $hashtag->increment('usage_count');
                $tagIds[] = $hashtag->id;
            }
        }
        if (!empty($tagIds)) {
            $post->hashtags()->sync($tagIds);
        }

        // ================= XỬ LÝ NÉN ẢNH THUẦN CHẤP HẾT CÁC ĐỊNH DẠNG =================
        // 5. Xử lý ảnh nâng cao (Không cần mimes, tự lọc file lỗi/file lạ)
        if ($request->hasFile('images')) {
            if (!file_exists(public_path('uploads/posts'))) {
                mkdir(public_path('uploads/posts'), 0777, true);
            }

            foreach ($request->file('images') as $file) {
                // Tự động đọc nội dung để kiểm tra cấu trúc ảnh (jfif, webp, png, jpg... chấp hết)
                $sourceImage = @imagecreatefromstring(file_get_contents($file->getRealPath()));

                // 🔥 NẾU LÀ FILE LỖI HOẶC ĐUÔI ĐỘC LẠ (NHƯ HEIC) KHÔNG ĐỌC ĐƯỢC:
                if ($sourceImage === false) {
                    // Trả về lỗi luôn cho client mà không thèm lưu, đỡ phiền phức!
                    return response()->json([
                        'message' => 'Một trong số các file bạn tải lên không đúng định dạng ảnh hiển thị được!'
                    ], 422);
                    // Nếu bạn dùng form submit thường (không phải AJAX) thì thay dòng trên bằng:
                    // return back()->withErrors(['images' => 'Định dạng ảnh không hỗ trợ!']);
                }

                // Tên file và đường dẫn lưu (Đồng bộ đuôi .jpg)
                $filename = time() . '_' . uniqid() . '.jpg';
                $destinationPath = public_path('uploads/posts/' . $filename);

                // Thu nhỏ kích thước nếu ảnh quá to (Ví dụ ảnh 5MB)
                $origWidth = imagesx($sourceImage);
                $origHeight = imagesy($sourceImage);
                $maxWidth = 1000;

                if ($origWidth > $maxWidth) {
                    $ratio = $maxWidth / $origWidth;
                    $newWidth = $maxWidth;
                    $newHeight = (int)($origHeight * $ratio);

                    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

                    imagealphablending($resizedImage, false);
                    imagesavealpha($resizedImage, true);

                    imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
                    imagedestroy($sourceImage);
                    $sourceImage = $resizedImage;
                }

                // Nén chất lượng xuống 65% thành file JPG siêu nhẹ
                imagejpeg($sourceImage, $destinationPath, 65);
                imagedestroy($sourceImage); // Giải phóng RAM

                // Lưu vào database
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

        // Kiểm tra xung đột dữ liệu (Concurrency control)
        if ($request->has('last_updated_at') && $post->updated_at != $request->last_updated_at) {
            return response()->json(['message' => 'Conflict'], 409);
        }

        // Kiểm tra quyền sở hữu bài viết
        if ($post->user_id != Auth::id()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            abort(403, 'Bạn không có quyền sửa bài viết này.');
        }

        // 1. Validate mở rộng max:10240 và bỏ hẳn mimes giống phần store
        $request->validate([
            'content' => 'required|string|max:550',
            'images.*' => 'nullable|image|max:10240', // Cho phép up ảnh tối đa 10MB trước khi nén
        ]);

        $post->content = $request->content;
        $post->save();

        // 2. Xử lý cập nhật lại Hashtag
        $tagNames = $hashtagService->getHashtags($post->content);
        $tagIds = [];

        if (!empty($tagNames)) {
            foreach ($tagNames as $tagName) {
                $hashtag = \App\Models\Hashtag::firstOrCreate([
                    'name' => mb_strtolower($tagName, 'UTF-8')
                ]);
                $hashtag->increment('usage_count');
                $tagIds[] = $hashtag->id;
            }
        }
        $post->hashtags()->sync($tagIds);

        // 3. Xử lý NÉN ẢNH THUẦN (Chấp hết định dạng jfif, webp, png, jpg...)
        if ($request->hasFile('images')) {
            if (!file_exists(public_path('uploads/posts'))) {
                mkdir(public_path('uploads/posts'), 0777, true);
            }

            // Bước A: Quét và dọn dẹp sạch sẽ các file ảnh cũ trên ổ đĩa vật lý để tránh rác server
            foreach ($post->media as $oldMedia) {
                $oldFilePath = public_path($oldMedia->media_url);
                if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                    @unlink($oldFilePath);
                }
            }
            // Xóa sạch các liên kết cũ trong bảng post_media
            $post->media()->delete();

            // Bước B: Lặp qua từng file ảnh mới gửi lên để nén
            foreach ($request->file('images') as $file) {
                $sourceImage = @imagecreatefromstring(file_get_contents($file->getRealPath()));

                if ($sourceImage === false) {
                    continue; // Nếu gặp tấm nào lỗi cấu trúc thì bỏ qua, xử lý tấm tiếp theo
                }

                // Đổi toàn bộ tên thành đuôi .jpg
                $filename = time() . '_' . uniqid() . '.jpg';
                $destinationPath = public_path('uploads/posts/' . $filename);

                // Resize chiều rộng về 1000px nếu ảnh gốc quá to (như ảnh 5MB)
                $origWidth = imagesx($sourceImage);
                $origHeight = imagesy($sourceImage);
                $maxWidth = 1000;

                if ($origWidth > $maxWidth) {
                    $ratio = $maxWidth / $origWidth;
                    $newWidth = $maxWidth;
                    $newHeight = (int)($origHeight * $ratio);

                    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                    imagealphablending($resizedImage, false);
                    imagesavealpha($resizedImage, true);

                    imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
                    imagedestroy($sourceImage);
                    $sourceImage = $resizedImage;
                }

                // Nén chất lượng 65% thành file siêu nhẹ
                imagejpeg($sourceImage, $destinationPath, 65);
                imagedestroy($sourceImage);

                // Thêm mới từng dòng vào database
                $post->media()->create([
                    'media_url' => 'uploads/posts/' . $filename,
                    'media_type' => 'photo',
                ]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Success'], 200);
        }

        return redirect()->back()->with('success', 'Cập nhật bài viết thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if ($request->has('last_updated_at') && (string)$post->updated_at !== $request->last_updated_at) {
            return response()->json(['error' => 'Dữ liệu không đồng bộ'], 409);
        }

        $post->delete();

        return response()->json(['success' => 'Xóa thành công']);
    }
    public function toggleLike($postId)
    {
        $post = \App\Models\Post::find($postId);
        $userId = Auth::id();
        if (!$userId)
            return response()->json(['error' => 'Unauthenticated'], 401);

        // Tìm xem đã like chưa
        $likeQuery = \App\Models\Like::where('post_id', $postId)->where('user_id', $userId);

        if ($likeQuery->exists()) {
            // Trường hợp BỎ LIKE
            $likeQuery->delete();
            $isLiked = false;
        } else {
            // Trường hợp LIKE MỚI
            \App\Models\Like::create([
                'post_id' => $postId,
                'user_id' => $userId
            ]);
            $isLiked = true;
        }

        // Đếm lại tổng số tim
        $count = \App\Models\Like::where('post_id', $postId)->count();

        event(new \App\Events\PostLiked(Auth::user(), $post, $isLiked));
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

        $posts->getCollection()->transform(function ($post) {
            if ($post->user) {
                $post->user->can_show_activity = $post->user->canShowActivityTo(Auth::user());
            }

            return $post;
        });

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

        // THAY ĐỔI Ở ĐÂY: Nếu bài được chọn đã là bài share, lấy luôn ID của bài gốc đầu tiên
        $sharedPost->parent_id = $originalPost->parent_id ? $originalPost->parent_id : $originalPost->id;

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
