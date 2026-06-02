<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    // Cho phép thêm dữ liệu vào 2 cột này
    protected $fillable = ['blocker_id', 'blocked_id'];
}