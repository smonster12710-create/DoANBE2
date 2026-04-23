<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hashtag extends Model
{
    // Khai báo các cột được phép thêm/sửa cho an toàn
    protected $fillable = ['name', 'usage_count'];

    // Thiết lập quan hệ Many-to-Many với bài viết (nếu sau này Pro cần)
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_hashtags');
    }
}
