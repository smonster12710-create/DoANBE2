<?
namespace App\Events;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostMention
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mentioner; // Đại ca đi thả tim
    public $post; // Bài viết được thả tim
    public $mentionedUser; // Người bị mention

    public function __construct($mentioner, $post, $mentionedUser)
    {
        $this->mentioner = $mentioner;
        $this->post = $post;
        $this->mentionedUser = $mentionedUser;
    }
}