<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use Faker\Factory as Faker;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Lấy danh sách ID của tất cả user hiện có trong DB
        $userIds = User::pluck('id')->toArray();

        // Kiểm tra nhẹ: Phải có ít nhất 2 User mới chơi trò gửi thông báo qua lại được
        if (count($userIds) < 2) {
            $this->command->info('Trong bảng users phải có ít nhất 2 người mới seed thông báo được nha!');
            return;
        }

        // Khai báo mấy loại thông báo anh em mình đang xài
        $types = ['like_post', 'comment', 'mention'];

        // Cày cuốc tạo ra 50 cái thông báo ảo
        for ($i = 0; $i < 50; $i++) {
            // 1. Chọn ngẫu nhiên 1 người nhận
            $userId = $faker->randomElement($userIds);

            // 2. Chọn ngẫu nhiên 1 người gửi (Phải loại cái ông $userId ra để không tự thông báo cho mình)
            $actorIds = array_diff($userIds, [$userId]);
            $actorId = $faker->randomElement($actorIds);

            // 3. Insert thẳng vô DB
            Notification::create([
                'user_id' => $userId,
                'actor_id' => $actorId,
                'type' => $faker->randomElement($types),
                'reference_id' => $faker->numberBetween(1, 20), // Giả sử ID bài viết/comment từ 1-20
                'is_read' => $faker->boolean(30), // Tỉ lệ: 30% là đã đọc (1), 70% là chưa đọc (0) cho ra dáng test
                'created_at' => $faker->dateTimeBetween('-1 week', 'now'), // Rải rác trong 1 tuần nay
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Đã import thành công 50 thông báo ảo vô DB !');
    }
}