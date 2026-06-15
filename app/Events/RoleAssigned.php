<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $playerId,
        public string $roleKey,
        public string $faction,
        public ?int $nightOrder,
        public array $abilities,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('player.'.$this->playerId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'role_key' => $this->roleKey,
            'faction' => $this->faction,
            'night_order' => $this->nightOrder,
            'abilities' => $this->abilities,
        ];
    }
}
