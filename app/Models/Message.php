<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';
    protected $fillable = ['conversation_id', 'sender_id', 'content', 'image_url', 'is_read'];

    // Quan hệ với người gửi
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}