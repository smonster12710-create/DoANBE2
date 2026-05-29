<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy danh sách ID của tất cả user hiện có trong DB
        $userIds = User::pluck('id')->toArray();

        // Kiểm tra nhẹ như cũ cho chắc ăn
        if (count($userIds) < 2) {
            $this->command->info('Trong bảng users phải có ít nhất 2 người mới seed thông báo được nha!');
            return;
        }

        // Tạo ra 50 thông báo ảo dùng Factory
        for ($i = 0; $i < 50; $i++) {
            $userId = fake()->randomElement($userIds);

            // Logic bảnh tỏn của Pro: Loại ông $userId ra khỏi danh sách người gửi
            $actorIds = array_diff($userIds, [$userId]);
            $actorId = fake()->randomElement($actorIds);

            // Gặp Factory là dập lệnh tạo liền, vừa đẹp vừa kích hoạt đầy đủ Eloquent Events luôn
            Notification::factory()->create([
                'user_id' => $userId,
                'actor_id' => $actorId,
            ]);
        }

        $this->command->info('Đã dùng Factory import thành công 50 thông báo ảo vô DB ');
    }
}