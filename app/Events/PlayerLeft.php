<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerLeft implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $roomId,
        public int $playerId,
        public int $playerCount,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('room.'.$this->roomId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'player_id' => $this->playerId,
            'player_count' => $this->playerCount,
        ];
    }
}
