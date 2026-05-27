<?
namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserFollowed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $follower; // Thằng đi bấm nút
    public $targetUser; // Thằng được theo dõi
    public $isFollowing; // true: follow mới, false: hủy follow

    public function __construct($follower, $targetUser, $isFollowing)
    {
        $this->follower = $follower;
        $this->targetUser = $targetUser;
        $this->isFollowing = $isFollowing;
    }
}