<?php

namespace App\Http\Controllers;

use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BlockController extends Controller
{
    public function toggleBlock($userId)
    {
        $blocker = Auth::user();

        // 1. Kiểm tra xem đã chặn chưa
        $isBlocked = $blocker->isBlocking($userId);

        if ($isBlocked) {
            // BỎ CHẶN
            Block::where('blocker_id', $blocker->id)
                ->where('blocked_id', $userId)
                ->delete();
            return back()->with('success', 'Đã bỏ chặn.');
        } else {
            // CHẶN
            Block::create([
                'blocker_id' => $blocker->id,
                'blocked_id' => $userId
            ]);

            // 2. TỰ ĐỘNG XÓA KẾT BẠN (Dựa trên bảng 'friendships' trong Model User)
            DB::table('friendships')->where(function ($q) use ($blocker, $userId) {
                $q->where('user_id', $blocker->id)->where('friend_id', $userId);
            })->orWhere(function ($q) use ($blocker, $userId) {
                $q->where('user_id', $userId)->where('friend_id', $blocker->id);
            })->delete();

            // 3. TỰ ĐỘNG XÓA THEO DÕI (Dựa trên bảng 'follows' trong Model User)
            DB::table('follows')->where(function ($q) use ($blocker, $userId) {
                $q->where('follower_id', $blocker->id)->where('following_id', $userId);
            })->orWhere(function ($q) use ($blocker, $userId) {
                $q->where('follower_id', $userId)->where('following_id', $blocker->id);
            })->delete();

            return back()->with('success', 'Đã chặn người dùng.');
        }
    }

    public function index()
    {
        // Lấy tất cả những người mà user này đã chặn
        $blockedUsers = \App\Models\User::whereIn('id', function ($query) {
            $query->select('blocked_id')
                ->from('blocks')
                ->where('blocker_id', auth()->id());
        })->get();

        return view('social.blocked_users', compact('blockedUsers'));
    }
}
