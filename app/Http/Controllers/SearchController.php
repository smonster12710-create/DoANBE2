<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Hashtag;

class SearchController extends Controller
{
    /**
     * Logic Search User
     */
    public function searchUsers(Request $request)
    {
        // Lấy từ khóa search từ request (?q=abc)
        $keyword = $request->input('q');

        // Nếu keyword rỗng thì trả về mảng rỗng cho nhẹ server
        if (empty($keyword)) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        // Query tìm user
        $users = User::where('is_active', 1) //  acc đang hoạt động
            ->where(function ($query) use ($keyword) {
                // Search tương đối (LIKE) trên cả username và fullname
                $query->where('username', 'LIKE', "%{$keyword}%")
                    ->orWhere('fullname', 'LIKE', "%{$keyword}%");
            })
            ->select('id', 'username', 'fullname', 'avatar_url', 'role')
            ->paginate(5);
    
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Logic Search Hashtag
     */
    public function searchHashtags(Request $request)
    {
        $keyword = $request->input('q');

        if (empty($keyword)) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        // Xóa dấu # ở đầu nếu user lỡ tay gõ luôn dấu # vô ô search
        $cleanKeyword = ltrim($keyword, '#');

        // Bỏ comment khúc này khi Pro đã có bảng hashtags nghen
        $hashtags = Hashtag::where('name', 'LIKE', "%{$cleanKeyword}%")
            ->orderBy('usage_count', 'desc') // Đếm số bài viết đang xài hashtag này
            ->select('id', 'name', 'usage_count') // Thằng nào trending đưa lên đầu
            ->paginate(5);

        return response()->json([
            'success' => true,
            'data' => $hashtags
        ]);
    }
}
