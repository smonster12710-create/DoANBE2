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

        $this->command->info('Đang xuất 1000 cái hashtags');

        // 1. TẠO 1000 HASHTAG
        for ($i = 0; $i < 1000; $i++) {
            $hashtags[] = [
                // Random tên hashtag (thêm $i để bảo đảm 1000 cái không bị trùng văng lỗi Unique)
                'name' => str_replace(' ', '', $faker->unique()->words(2, true)) . '_' . $i,
                // Random số đếm từ 10 đến 50.000 để test cái vụ 1.5k, 50k trên giao diện
                'usage_count' => rand(10, 50000),
            ];
        }

        // Dùng chiêu Chunk (Cắt lô) chèn 500 dòng 1 lần cho nó lẹ, ko bị nghẽn RAM
        foreach (array_chunk($hashtags, 500) as $chunk) {
            DB::table('hashtags')->insert($chunk);
        }

        // ==========================================
        // 2. LIÊN KẾT BÀI VIẾT (Tạo data cho bảng Pivot)
        // ==========================================
        $postIds = DB::table('posts')->pluck('id')->toArray();

        // Chốt chặn: Nếu chưa có bài post nào thì ngưng, khỏi làm bước 2
        if (empty($postIds)) {
            $this->command->error('Ủa khoan, bảng posts đang trống lóc! Pro cần seed bài viết trước mới liên kết được nha!');
            return;
        }

        $hashtagIds = DB::table('hashtags')->pluck('id')->toArray();
        $pivotData = [];

        $this->command->info('Đang móc nối Hashtag với Post...');

        // Random tạo khoảng 3000 lượt gắn tag vô bài viết
        for ($i = 0; $i < 3000; $i++) {
            $pivotData[] = [
                'post_id' => $faker->randomElement($postIds),
                'hashtag_id' => $faker->randomElement($hashtagIds),
            ];
        }

        // Lọc trùng lặp (1 bài post không thể gắn 1 hashtag 2 lần) để né lỗi SQL
        $pivotData = collect($pivotData)->unique(function ($item) {
            return $item['post_id'] . '_' . $item['hashtag_id'];
        })->toArray();

        // Chèn lô vô bảng trung gian (Tên bảng thường là hashtag_post)
        // Đổi hàm insert() thành insertOrIgnore()
        foreach (array_chunk($pivotData, 500) as $chunk) {
            DB::table('post_hashtags')->insertOrIgnore($chunk); // SỬA NGAY DÒNG NÀY
        }
        $this->command->info('Seed xong 1000 hashtags mượt rượt rồi Pro ơi! 🚀');
    }
}