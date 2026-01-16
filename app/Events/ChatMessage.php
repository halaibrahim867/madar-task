<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ChatMessage implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public int $userId;
    public string $message;

    public function __construct(User $user, string $message)
    {
        $this->userId = $user->id;
        $this->message = $message;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('chat.' . $this->userId);
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'message' => $this->message,
        ];
    }
}
