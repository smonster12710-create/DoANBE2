<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hashtag extends Model
{
    public $timestamps = false;
    // Khai báo các cột được phép thêm/sửa 
    protected $fillable = ['name', 'usage_count'];

    // Thiết lập quan hệ Many-to-Many với bài viết 
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_hashtags');
    }
}
