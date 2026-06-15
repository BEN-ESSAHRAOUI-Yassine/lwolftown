<?php

namespace App\Livewire\Player;

use App\Models\Room;
use Livewire\Component;

class PlayerLobby extends Component
{
    public Room $room;
    public $players;

    public $pollInterval = 3000;

    public function mount(Room $room): void
    {
        $this->room = $room;
        $this->loadPlayers();
    }

    public function loadPlayers(): void
    {
        $this->players = $this->room->players()->orderBy('created_at')->get();
    }

    public function render()
    {
        return view('livewire.player.player-lobby')->layout('layouts.app');
    }
}
