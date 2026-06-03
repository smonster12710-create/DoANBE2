<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Story;
use Illuminate\Support\Facades\Storage;

class CleanupExpiredStories extends Command
{
    // Tên lệnh để mốt mình gọi trong terminal
    protected $signature = 'stories:cleanup';

    // Mô tả 
    protected $description = 'Quét và xóa vĩnh viễn các Story đã quá hạn 24h (xóa cả file ổ cứng)';

    public function handle()
    {
        // 1. Lùng sục tìm mấy cái Story có giờ 'expires_at' nhỏ hơn giờ hiện tại (đã chết)
        $expiredStories = Story::where('expires_at', '<', now())->get();

        $count = 0;
        foreach ($expiredStories as $story) {
            // 2. Thọc tay vô ổ cứng (storage/app/public) xóa cái file hình/video đi
            if (Storage::disk('public')->exists($story->media_path)) {
                Storage::disk('public')->delete($story->media_path);
            }

            // 3. Xóa khỏi bảng Database
            $story->delete();
            $count++;
        }

        // Báo cáo thành tích
        $this->info("Đã quét dọn sạch sẽ {$count} story hết hạn! 🧹");
    }
}