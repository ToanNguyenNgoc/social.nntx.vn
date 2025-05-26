<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;

class SubscribeChat implements ShouldBroadcast
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
        Log::info('chat', ['data' => 'private-subscribe-chat.user_id.' . $this->user_id]);
        return ['private-subscribe-chat.user_id.' . $this->user_id];
    }

    public function broadcastAs()
    {
        return 'emit-subscribe-chat';
    }

    public function broadcastWith()
    {
        return ['message' => $this->message];
    }
}
