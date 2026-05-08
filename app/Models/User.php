<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'fullname',
        'gender',
        'phone',
        'bio',
        'birthday',
        'address',
        'avatar_url',
        'cover_url',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id', 'id');
    }
}