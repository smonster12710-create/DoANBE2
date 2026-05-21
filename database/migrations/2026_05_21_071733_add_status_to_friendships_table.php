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
        Schema::table('friendships', function (Blueprint $table) {
            // Thêm cột status vào sau cột friend_id, mặc định là 'pending'
            $table->enum('status', ['pending', 'accepted'])->default('pending')->after('friend_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('friendships', function (Blueprint $table) {
            // Xóa cột status nếu rollback
            $table->dropColumn('status');
        });
    }
};
