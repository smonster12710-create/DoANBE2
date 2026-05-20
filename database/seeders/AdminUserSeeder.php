<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminUserSeeder extends Seeder
{
    /**
     * Tao hoac cap nhat tai khoan admin mac dinh.
     * Seeder nay uu tien update theo email de khong tao trung admin.
     */
    public function run(): void
    {
        // Du lieu co ban cua tai khoan admin he thong.
        $data = [
            'fullname' => 'Admin ESPACE',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'role' => 'admin',
            'is_active' => 1,
            'updated_at' => now(),
        ];

        // Ho tro ca hai kieu cot mat khau neu database dang co cau truc khac nhau.
        if (Schema::hasColumn('users', 'password')) {
            $data['password'] = Hash::make('123');
        }

        if (Schema::hasColumn('users', 'password_hash')) {
            $data['password_hash'] = Hash::make('123');
        }

        $exists = DB::table('users')
            ->where('email', 'admin@gmail.com')
            ->first();

        // Neu admin da ton tai thi cap nhat, neu chua co thi tao moi.
        if ($exists) {
            DB::table('users')
                ->where('email', 'admin@gmail.com')
                ->update($data);
        } else {
            $data['created_at'] = now();

            DB::table('users')->insert($data);
        }
    }
}
