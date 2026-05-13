<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Notification extends Model
{
    protected $fillable = ['user_id', 'actor_id', 'type', 'reference_id', 'is_read'];
    //Lay thong tin nguoi tao thong bao
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    //Lay thong tin nguoi thuc hien hanh dong 
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}