<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    // Laravel mặc định tìm cột 'id', bảng bạn không có nên phải tắt nó đi
    public $incrementing = false;
    protected $primaryKey = null;

    // Chỉ định các cột được phép thêm dữ liệu nhanh
    protected $fillable = ['user_id', 'post_id'];

    // Nếu bảng chỉ có created_at mà không có updated_at, hãy báo cho Laravel
    public const UPDATED_AT = null;
}
