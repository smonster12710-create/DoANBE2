<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ID người đăng Story
            $table->string('media_path'); // Đường dẫn file hình hoặc video lưu trên ổ cứng
            $table->string('media_type')->default('image'); // Để phân biệt 'image' hay 'video'
            $table->text('content')->nullable(); // Chữ chèn vô Story (nếu có)
            $table->timestamp('expires_at'); // Cột tử thần chốt hạn đúng 24h là bay màu
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
