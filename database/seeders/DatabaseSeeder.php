<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            // 1. Users
            $uId = DB::table('users')->insertGetId([
                'username' => "user_$i",
                'email' => "user$i@gmail.com",
                'password_hash' => bcrypt('123456'),
                'fullname' => "Người dùng $i",
                'avatar_url' => "https://picsum.photos/200/200?sig=user_$i",
                'cover_url' => "https://picsum.photos/800/300?sig=cover_$i",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Locations
            $locId = DB::table('locations')->insertGetId([
                'name' => "Quận $i, TP.HCM",
                'address' => "$i Đường Lê Lợi, Quận $i",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ĐÃ XÓA MỤC SỐ 3 (Hashtags fake) ĐỂ XÀI HÀNG XỊN Ở DƯỚI!

            // 4. Conversations
            $convId = DB::table('conversations')->insertGetId([
                'name' => "Nhóm học tập $i",
                'image_url' => "https://picsum.photos/100/100?sig=conv_$i",
                'type' => 'group',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 5. Conversation Participants
            DB::table('conversation_participants')->insert([
                'conversation_id' => $convId,
                'user_id' => $uId,
                'role' => 'admin',
                'joined_at' => now()
            ]);

            // 6. Posts
            $postId = DB::table('posts')->insertGetId([
                'user_id' => $uId,
                'content' => "Hôm nay mình học về Database Seeder trong Laravel cực hay! Bài số $i",
                'location_id' => $locId,
                'privacy' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 7. Post Media
            DB::table('post_media')->insert([
                'post_id' => $postId,
                'media_url' => "https://picsum.photos/600/400?sig=post_$i",
                'media_type' => 'photo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ĐÃ XÓA MỤC SỐ 8 (Gắn Post Hashtag fake)

            // 9. Messages
            DB::table('messages')->insert([
                'conversation_id' => $convId,
                'sender_id' => $uId,
                'content' => "Chào mọi người, đây là ảnh tài liệu nhé!",
                'image_url' => "https://picsum.photos/300/200?sig=msg_$i",
                'is_read' => false,
                'created_at' => now()
            ]);

            // 10. Comments
            DB::table('comments')->insert([
                'post_id' => $postId,
                'user_id' => $uId,
                'content' => "Ảnh minh họa cho bình luận của mình.",
                'image_url' => "https://picsum.photos/250/150?sig=cmt_$i",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 11. Likes
            DB::table('likes')->insertOrIgnore([
                'user_id' => $uId,
                'post_id' => $postId,
                'created_at' => now()
            ]);

            // 12. Follows
            if ($uId > 1) {
                DB::table('follows')->insertOrIgnore([
                    'follower_id' => $uId,
                    'following_id' => $uId - 1,
                    'created_at' => now()
                ]);
            }

            // 13. Notifications
            if ($uId > 1) {
                DB::table('notifications')->insert([
                    'user_id' => $uId,
                    'actor_id' => $uId - 1,
                    'type' => 'mention',
                    'reference_id' => $postId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 14. Reports
            if ($uId > 1) {
                DB::table('reports')->insert([
                    'user_id' => $uId,
                    'post_id' => $postId - 1,
                    'reason' => "Nội dung vi phạm tiêu chuẩn cộng đồng số $i",
                    'image_url' => "https://picsum.photos/400/300?sig=report_$i",
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // CHỐT HẠ: Triệu hồi 10 em Trending xịn xò vào cuối cùng
        $this->call([
            AdminUserSeeder::class,
            NotificationSeeder::class,
            HashtagSeeder::class, // <-- Bùa gọi Trending nằm đây nè Pro!
        ]);
    }
}