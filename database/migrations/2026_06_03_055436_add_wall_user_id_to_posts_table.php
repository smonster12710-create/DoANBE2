<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            // Thêm cột wall_user_id cho phép null (vì đăng ở trang chủ sẽ không có wall_user_id)
            $table->unsignedBigInteger('wall_user_id')->nullable()->after('user_id');

            // Tạo liên kết khóa ngoại đến bảng users (tùy chọn nhưng nên có)
            $table->foreign('wall_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['wall_user_id']);
            $table->dropColumn('wall_user_id');
        });
    }
};
