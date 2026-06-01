<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade'); // Liên kết tới bảng nhóm
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Liên kết tới bảng người dùng
            $table->enum('role', ['admin', 'moderator', 'member'])->default('member'); // Vai trò trong nhóm
            $table->enum('status', ['pending', 'approved'])->default('approved'); // Chờ duyệt hay đã là thành viên chính thức
            $table->timestamps();

            // Đảm bảo một người dùng không bị trùng lặp dữ liệu trong một nhóm
            $table->unique(['group_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_members');
    }
};
