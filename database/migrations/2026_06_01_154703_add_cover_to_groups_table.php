<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            // Thêm cột cover cho phép nhận giá trị null (nếu nhóm không đổi ảnh bìa)
            $table->string('cover')->nullable()->after('description'); 
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('cover');
        });
    }
};