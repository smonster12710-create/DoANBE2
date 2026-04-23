<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    // Khai báo tên bảng (vì Laravel thường tự tìm bảng số nhiều 'post_media' nên cái này có thể ko cần, nhưng ghi vào cho chắc)
    protected $table = 'post_media';

    // Cho phép lưu dữ liệu vào các cột này
    protected $fillable = ['post_id', 'media_url', 'media_type'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
