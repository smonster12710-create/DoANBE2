<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    // Đảm bảo tên bảng trùng với thực tế trong ảnh
    protected $table = 'post_media';

    // Cho phép lưu các cột này
    protected $fillable = [
        'post_id',
        'media_url',
        'media_type'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
