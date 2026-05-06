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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable();
            }

            if (!Schema::hasColumn('users', 'birthday')) {
                $table->date('birthday')->nullable();
            }

            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable();
            }

            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable();
            }

            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable();
            }
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'bio')) {
                $table->dropColumn('bio');
            }

            if (Schema::hasColumn('users', 'birthday')) {
                $table->dropColumn('birthday');
            }

            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }

            if (Schema::hasColumn('users', 'gender')) {
                $table->dropColumn('gender');
            }

            if (Schema::hasColumn('users', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
};