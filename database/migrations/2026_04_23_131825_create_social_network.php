<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. NGƯỜI DÙNG (Users)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->text('password_hash');
            $table->string('fullname', 100)->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('avatar_url')->nullable();
            $table->text('cover_url')->nullable();
            $table->string('role', 20)->default('user');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. ĐỊA ĐIỂM (Locations)
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        // 3. NHÃN (Hashtags)
        Schema::create('hashtags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->integer('usage_count')->default(0);
        });

        // 4. CUỘC HỘI THOẠI (Conversations)
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('image_url')->nullable(); // Ảnh đại diện nhóm
            $table->enum('type', ['private', 'group'])->default('private');
            $table->timestamps();
        });

        // 5. BÀI VIẾT (Posts) - ĐÃ BỎ CỘT ẢNH TRỰC TIẾP
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content')->nullable();
            $table->tinyInteger('privacy')->default(0);
            $table->integer('like_count')->default(0);
            $table->integer('comment_count')->default(0);
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('set null');
            $table->timestamps();
        });

        // 6. THEO DÕI (Follows)
        Schema::create('follows', function (Blueprint $table) {
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('following_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['follower_id', 'following_id']);
        });

        // 7. TIN NHẮN (Messages)
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->text('content')->nullable();
            $table->text('image_url')->nullable(); // Ảnh gửi kèm tin nhắn
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        // 8. THÀNH VIÊN NHÓM CHAT (Participants)
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['admin', 'member'])->default('member');
            $table->timestamp('joined_at')->useCurrent();
            $table->primary(['conversation_id', 'user_id']);
        });

        // 9. BÌNH LUẬN (Comments)
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content')->nullable();
            $table->text('image_url')->nullable(); // Ảnh bình luận
            $table->timestamps();
        });

        // 10. LƯỢT THÍCH (Likes)
        Schema::create('likes', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['user_id', 'post_id']);
        });

        // 11. ẢNH BÀI VIẾT CHI TIẾT (Post Media) - Dùng bảng này thay cho cột ảnh ở bảng Post
        Schema::create('post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->text('media_url');
            $table->string('media_type', 20); // photo, video
            $table->timestamps();
        });

        // 12. HASHTAG BÀI VIẾT (Post Hashtags)
        Schema::create('post_hashtags', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('hashtag_id')->constrained('hashtags')->onDelete('cascade');
            $table->primary(['post_id', 'hashtag_id']);
        });

        // 13. THÔNG BÁO (Notifications)
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('actor_id')->constrained('users')->onDelete('cascade');
            $table->string('type');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // 14. BÁO CÁO (Reports)
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->text('reason');
            $table->text('image_url')->nullable(); // Ảnh bằng chứng báo cáo
            $table->enum('status', ['pending', 'resolved', 'dismissed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('post_hashtags');
        Schema::dropIfExists('post_media');
        Schema::dropIfExists('likes');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('follows');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('hashtags');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('users');
    }
};
