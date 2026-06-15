<?php

namespace App\Events;

use App\Models\Player;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerJoined implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Player $player,
        public int $playerCount,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('room.'.$this->player->room_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'player' => [
                'id' => $this->player->id,
                'nickname' => $this->player->nickname,
                'is_narrator' => $this->player->is_narrator,
            ],
            'player_count' => $this->playerCount,
        ];
    }
}
