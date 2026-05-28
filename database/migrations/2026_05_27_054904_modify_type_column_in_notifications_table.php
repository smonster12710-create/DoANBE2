<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // Viết SQL thuần để cập nhật lại danh sách ENUM
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('like','like_post', 'comment', 'mention', 'follow',
'friend_request') NOT NULL");

        // DB::statement("ALTER TABLE notifications MODIFY COLUMN type VARCHAR(50) NOT NULL");
    }

    public function down()
    {
        // Chừa đường lui: Đưa nó về lại 3 thằng ban đầu lỡ có biến
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('like','like_post', 'comment', 'mention') NOT NULL");
    }
};