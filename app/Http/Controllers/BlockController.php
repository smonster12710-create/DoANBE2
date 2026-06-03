<?php

namespace App\Http\Controllers;

use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BlockController extends Controller
{
    public function toggleBlock(Request $request, $userId)
    {
        $blocker = Auth::user();
        $target = \App\Models\User::findOrFail($userId); // Lấy thông tin đối tượng để hiển thị tên
        // --- [MỚI] KIỂM TRA ĐỒNG BỘ ---
        // So sánh trạng thái thực tế trong DB với trạng thái mà form tin rằng đang xảy ra
        $isActuallyBlocked = $blocker->isBlocking($userId);
        $expectedStatus = $request->input('expected_status'); // Giá trị từ input hidden

        if (($isActuallyBlocked && $expectedStatus === 'not_blocked') ||
            (!$isActuallyBlocked && $expectedStatus === 'blocked')
        ) {
            return back()->with('error', 'Trạng thái đã thay đổi, trang sẽ tự tải lại.');
        }
        $isBlocked = $isActuallyBlocked;

        if ($isBlocked) {
            // BỎ CHẶN
            Block::where('blocker_id', $blocker->id)
                ->where('blocked_id', $userId)
                ->delete();

            // Trả về thông báo "Đã bỏ chặn..."
            return back()->with('success', 'Đã bỏ chặn ' . $target->fullname);
        } else {
            // CHẶN
            Block::create([
                'blocker_id' => $blocker->id,
                'blocked_id' => $userId
            ]);

            // [Giữ nguyên logic xóa friend/follow của bạn...]
            DB::table('friendships')->where(function ($q) use ($blocker, $userId) {
                $q->where('user_id', $blocker->id)->where('friend_id', $userId);
            })->orWhere(function ($q) use ($blocker, $userId) {
                $q->where('user_id', $userId)->where('friend_id', $blocker->id);
            })->delete();

            DB::table('follows')->where(function ($q) use ($blocker, $userId) {
                $q->where('follower_id', $blocker->id)->where('following_id', $userId);
            })->orWhere(function ($q) use ($blocker, $userId) {
                $q->where('follower_id', $userId)->where('following_id', $blocker->id);
            })->delete();

            // Trả về thông báo "Đã chặn..."
            return back()->with('success', 'Đã chặn ' . $target->fullname);
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
