<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['content', 'user_id', 'post_id', 'is_anonymous'];

    protected $casts = [
        'is_anonymous' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    public function getFormattedContentAttribute()
    {
        // 1. BƯỚC CỰC KỲ QUAN TRỌNG: Chống hack XSS! 
        // Phải dùng hàm e() để rào tụi hacker gõ mã độc <script> vô bình luận
        $safeContent = e($this->content);

        // 2. Dùng Regex lùng sục mấy chữ bắt đầu bằng @
        $pattern = '/(?<=^|\s)@([\p{L}\p{N}_]+)/u';

        // 3. Biến nó thành thẻ <a> màu xanh. 
        // LƯU Ý: Chỗ href="/profile/$1", Pro nhớ thay bằng cái đường dẫn thật tới trang cá nhân trong dự án của Pro nha.
        $replacement = '<a href="/profile/$1" class="text-primary text-decoration-none fw-bold hover-underline">@$1</a>';

        // 4. Trả về thành phẩm đã "xào nấu"
        return preg_replace($pattern, $replacement, $safeContent);
    }
}
