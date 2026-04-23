<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            // 1. Users
            $uId = DB::table('users')->insertGetId([
                'username' => "user_$i",
                'email' => "user$i@gmail.com",
                'password_hash' => bcrypt('123456'),
                'fullname' => "Người dùng $i",
                'created_at' => now(),
            ]);

            // 2. Locations
            $locId = DB::table('locations')->insertGetId(['name' => "Quận $i, TP.HCM"]);

            // 3. Hashtags
            $hashId = DB::table('hashtags')->insertGetId(['name' => "trending_topic_$i"]);

            // 4. Conversations
            $convId = DB::table('conversations')->insertGetId([
                'name' => "Nhóm học tập $i",
                'type' => 'group',
                'created_at' => now()
            ]);

            // 5. Conversation Participants (Bảng trung gian 1)
            DB::table('conversation_participants')->insert([
                'conversation_id' => $convId,
                'user_id' => $uId,
                'joined_at' => now()
            ]);

            // 6. Posts
            $postId = DB::table('posts')->insertGetId([
                'user_id' => $uId,
                'content' => "Học Laravel thú vị quá nè mọi người ơi #$i",
                'location_id' => $locId,
                'created_at' => now(),
            ]);

            // 7. Post Media
            DB::table('post_media')->insert([
                'post_id' => $postId,
                'media_url' => "https://picsum.photos/400/300?sig=$i",
                'media_type' => 'image',
                'sort_order' => 1
            ]);

            // 8. Post Hashtags (Bảng trung gian 2)
            DB::table('post_hashtags')->insert([
                'post_id' => $postId,
                'hashtag_id' => $hashId
            ]);

            // 9. Messages
            DB::table('messages')->insert([
                'conversation_id' => $convId,
                'sender_id' => $uId,
                'content' => "Chào buổi sáng cả nhóm!",
                'created_at' => now()
            ]);

            // 10. Comments
            DB::table('comments')->insert([
                'post_id' => $postId,
                'user_id' => $uId,
                'content' => "Bài viết này hay quá!",
                'created_at' => now()
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
                    'user_id' => $uId,       // Người nhận là user hiện tại
                    'actor_id' => $uId - 1,  // Người tác động là user trước đó (đảm bảo id này đã tồn tại)
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
                    'post_id' => $postId - 1, // Báo cáo bài viết của vòng lặp trước
                    'reason' => "Nội dung không phù hợp $i",
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
