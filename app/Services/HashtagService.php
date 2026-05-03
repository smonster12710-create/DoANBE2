<?php

namespace App\Services;

use App\Models\Hashtag;
use App\Models\Post;

class HashtagService
{
    /**
     * Xử lý bóc tách và lưu hashtag cho một bài viết
     */
    public function processTags(Post $post)
    {
        // 1. Dùng Regex quét các hashtag bắt đầu bằng dấu # (Hỗ trợ tiếng Việt)
        $pattern = '/#([a-zA-Z0-9_À-ỹ]+)/u';
        preg_match_all($pattern, $post->content, $matches);

        $rawTags = $matches[1]; // Lấy cái ruột chữ, bỏ dấu #

        // 2. Làm sạch: Chuyển về chữ thường và loại bỏ các tag trùng lặp
        $cleanTags = array_unique(array_map(function ($tag) {
            return mb_strtolower($tag, 'UTF-8');
        }, $rawTags));

        // 3. Xử lý lưu Database
        $tagIds = [];
        foreach ($cleanTags as $tagName) {
            // Tìm xem tag có chưa, chưa có thì tự động tạo mới
            $hashtag = Hashtag::firstOrCreate(['name' => $tagName]);

            // Mẹo cho Pro: Kiểm tra xem bài viết này đã gắn tag này chưa
            // Nếu chưa gắn thì mới tăng usage_count (tránh bị tăng ảo khi Update bài)
            if (!$post->hashtags->contains($hashtag->id)) {
                $hashtag->increment('usage_count');
            }

            // Gom ID của hashtag này vào mảng
            $tagIds[] = $hashtag->id;
        }

        // 4. Đồng bộ (Sync) vào bảng trung gian post_hashtags
        //  Tự thêm tag mới, tự xóa tag cũ bị gỡ ra
        $post->hashtags()->sync($tagIds);
    }
}
