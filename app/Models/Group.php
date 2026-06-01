<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'avatar_url', 'cover_url', 'privacy', 'creator_id'];

    // Mối quan hệ: Một nhóm có nhiều thành viên (Lấy qua bảng trung gian group_members)
    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members')
                    ->withPivot('role', 'status')
                    ->withTimestamps();
    }

    // Mối quan hệ: Một nhóm có nhiều bài viết công khai bên trong
    public function posts()
    {
        return $this->hasMany(Post::class)->orderBy('created_at', 'desc');
    }

    // Mối quan hệ: Lấy thông tin tài khoản của Admin sáng lập nhóm
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}