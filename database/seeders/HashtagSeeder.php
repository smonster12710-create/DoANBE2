<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class HashtagSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $hashtags = [];

        $this->command->info('Đang xuất 10 cái trending hashtags siêu xịn...');

        // 1. TẠO 10 HASHTAG TRENDING
        $trendingNames = ['XuHuong', 'TinTuc', 'GiaiTri', 'TheThao', 'CongNghe', 'DuLich', 'AmThuc', 'KhamPha', 'ristiano_ronaldo', 'Drama'];

        for ($i = 0; $i < 10; $i++) {
            $hashtags[] = [
                'name' => $trendingNames[$i],
                // Trending thì view phải khủng, random từ 10k đến 500k luôn test UI cho phê
                'usage_count' => rand(10000, 500000),
            ];
        }

        // Có 10 dòng thì insert thẳng 1 phát luôn, dẹp cái trò array_chunk
        // SỬA LẠI THÀNH DÒNG NÀY CHO TUI:
        DB::table('hashtags')->insertOrIgnore($hashtags);

        // ==========================================
        // 2. LIÊN KẾT BÀI VIẾT (Tạo data cho bảng Pivot)
        // ==========================================
        $postIds = DB::table('posts')->pluck('id')->toArray();

        // Chốt chặn: Nếu chưa có bài post nào thì ngưng
        if (empty($postIds)) {
            $this->command->error('Ủa khoan, bảng posts đang trống lóc! Pro cần seed bài viết trước mới liên kết được nha!');
            return;
        }

        $hashtagIds = DB::table('hashtags')->pluck('id')->toArray();
        $pivotData = [];

        $this->command->info('Đang móc nối 10 Hashtag trending này vô mấy bài Post...');

        // Random tạo khoảng 50 lượt gắn tag 
        for ($i = 0; $i < 50; $i++) {
            $pivotData[] = [
                'post_id' => $faker->randomElement($postIds),
                'hashtag_id' => $faker->randomElement($hashtagIds),
            ];
        }

        // Lọc trùng lặp 
        $pivotData = collect($pivotData)->unique(function ($item) {
            return $item['post_id'] . '_' . $item['hashtag_id'];
        })->toArray();


        DB::table('post_hashtags')->insertOrIgnore($pivotData);

        $this->command->info('Seed xong 10 top trending ! 🚀');
    }
}