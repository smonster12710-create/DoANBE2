<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // parent_id sẽ lưu ID của bài viết gốc nếu đây là một bài share
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');

            // Tạo khóa ngoại liên kết ngược lại chính bảng posts
            $table->foreign('parent_id')->references('id')->on('posts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
