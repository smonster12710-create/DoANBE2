<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Hashtag;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function globalSearch(Request $request)
    {
        $keyword = $request->input('query');
        $keywordClean = ltrim($keyword, '#'); // Xóa dấu # nếu có

        // 1. Tìm User
        $users = User::where('username', 'LIKE', "%{$keyword}%")->limit(5)->get();

        // 2. Tìm Hashtag kèm theo bài viết của nó
        $hashtags = Hashtag::with(['posts' => function ($query) {
            $query->latest()->limit(3); // Chỉ lấy 3 bài mới nhất của mỗi tag cho nhẹ
        }, 'posts.user']) // Lấy luôn thông tin người đăng bài đó
            ->where('name', 'LIKE', "%{$keywordClean}%")
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'users' => $users,
                'hashtags' => $hashtags
            ]
        ]);
    }
}
