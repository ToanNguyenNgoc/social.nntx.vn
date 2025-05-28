<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;

class ChatEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $user_id;

    public function __construct(Message $message, int $user_id)
    {
        $this->message = $message;
        $this->user_id = $user_id;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('subscribe-topic_id.' . $this->message->topic_id),
        ];
    }

    public function broadcastAs()
    {
        return 'emit-topic';
    }

    public function broadcastWith()
    {
        return ['message' => $this->message];
    }
}
