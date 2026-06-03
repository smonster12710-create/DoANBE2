<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'media_path',
        'media_type',
        'content',
        'expires_at',
    ];

    // Ép kiểu thằng expires_at ra dạng ngày tháng cho dễ xài
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // Story này thuộc về thằng User nào?
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}