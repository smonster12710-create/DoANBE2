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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên nhóm (Ví dụ: Lập trình Laravel)
            $table->string('slug')->unique(); // Đường dẫn gọn sạch (Ví dụ: lap-trinh-laravel)
            $table->text('description')->nullable(); // Mô tả ngắn về nhóm
            $table->string('avatar_url')->nullable(); // Ảnh đại diện nhóm
            $table->string('cover_url')->nullable(); // Ảnh bìa nhóm
            $table->enum('privacy', ['public', 'private'])->default('public'); // Chế độ công khai hoặc riêng tư
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade'); // ID người sáng lập nhóm
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
