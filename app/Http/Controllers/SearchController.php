<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Hashtag;

class SearchController extends Controller
{
    /**
     * API: Tìm kiếm User (Trả về JSON cho AJAX gợi ý)
     */
    public function searchUsers(Request $request)
    {
        $keyword = $request->input('q');

        if (empty($keyword)) {
            return response()->json(['success' => true, 'data' => []]);
        }

        // Ép từ khóa về chữ thường để tìm cho chuẩn
        $keyword = mb_strtolower($keyword, 'UTF-8');

        // Tìm kiếm người dùng dựa trên username hoặc fullname, loại trừ admin
        $users = User::where('role', '!=', 'admin')
            ->where(function ($query) use ($keyword) {
                $query->where('username', 'LIKE', "%{$keyword}%")
                    ->orWhere('fullname', 'LIKE', "%{$keyword}%");
            })
            // Nếu Pro không chắc user đã active chưa thì tạm thời bỏ cái where('is_active', 1) để test nhé!
            ->select('id', 'username', 'fullname', 'avatar_url', 'role', 'show_activity_status', 'is_online', 'last_activity_at')
            ->paginate(5);

        $users->getCollection()->transform(function ($user) {
            $user->can_show_activity = $user->canShowActivityTo(auth()->user());
            $user->activity_status = $user->activityStatusFor(auth()->user());
            $user->avatar_src = $user->avatar_src;

            return $user;
        });

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * API: Tìm kiếm Hashtag (Trả về JSON cho AJAX gợi ý)
     */
    public function searchHashtags(Request $request)
    {
        $keyword = $request->input('q');

        if (empty($keyword)) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $cleanKeyword = mb_strtolower(ltrim($keyword, '#'), 'UTF-8');

        $hashtags = Hashtag::where('name', 'LIKE', "%{$cleanKeyword}%")
            ->withCount('posts') // Tự động đẻ ra biến posts_count
            ->orderBy('posts_count', 'desc')
            ->paginate(5);

        return response()->json(['success' => true, 'data' => $hashtags]);
    }

    /**
     * VIEW: Hiển thị trang bài viết của Hashtag (Trả về Blade View)
     * Đây là hàm để fix lỗi "BadMethodCallException" của Pro nè!
     */
    public function searchHashtag(Request $request)
    {
        $query = $request->query('q');
        $cleanKeyword = mb_strtolower(ltrim($query, '#'), 'UTF-8');

        if (empty($cleanKeyword)) {
            return redirect()->route('home');
        }

        // Tìm hashtag và lấy các bài viết liên quan
        $hashtag = Hashtag::where('name', $cleanKeyword)->first();

        $posts = $hashtag
            ? $hashtag->posts()->with(['user', 'media', 'likes'])->latest()->paginate(10)
            : collect();

        // Trả về view 'social.hashtag' mà anh em mình đã dựng
        return view('social.hashtag', [
            'posts' => $posts,
            'cleanKeyword' => $cleanKeyword
        ]);
    }

    public function showHashtag($name)
    {

        $hashtag = Hashtag::where('name', $name)->first();
        if (!$hashtag) {
            return redirect()->route('social')->with('error', 'Hashtag không tồn tại!');
        }
        // Bươi DB lấy những bài viết có chứa hashtag này
        // Khúc này tùy cấu trúc DB của Pro (có bảng trung gian hay dạng chuỗi)
        $posts = \App\Models\Post::whereHas('hashtags', function ($q) use ($name) {
            $q->where('name', $name);
        })->latest()->get();

        return view('social.hashtag', compact('posts', 'name'));
    }
}
