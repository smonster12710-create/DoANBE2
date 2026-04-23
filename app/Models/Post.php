<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    // Thêm đoạn này vào để cho phép lưu dữ liệu vào các cột tương ứng
    protected $fillable = [
        'user_id',
        'content',
        'privacy',
        'like_count',
        'comment_count'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->hasMany(PostMedia::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }
}
