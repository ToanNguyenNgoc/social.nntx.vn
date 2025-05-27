<?php

namespace App\Events;

use App\Models\NotificationLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    const NOTI_TYPE_START_FOLLOW = 1;

    /**
     * Create a new event instance.
     */

    private $message;
    private $payload_id;
    private $user_id;
    private $type;

    public function __construct(string $message, int $payload_id, int $user_id, int $type)
    {
        //
        $this->message = $message;
        $this->payload_id = $payload_id;
        $this->user_id = $user_id;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('subscribe-notification.user_id.' . $this->user_id),
        ];
    }

    public function broadcastAs()
    {
        return 'emit-notification';
    }

    public function broadcastWith()
    {
        $notification = NotificationLog::create([
            'message' => $this->message,
            'payload_id' => $this->payload_id,
            'type_id' => $this->type,
            'received_id' => $this->user_id
        ]);
        return ['message' => $notification];
    }
}
