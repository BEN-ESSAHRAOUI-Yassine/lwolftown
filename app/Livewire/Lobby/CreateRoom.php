<?php

namespace App\Livewire\Lobby;

use App\Game\Services\LobbyService;
use Livewire\Component;

class CreateRoom extends Component
{
    public string $nickname = '';

    public function submit(LobbyService $lobbyService)
    {
        $this->validate([
            'nickname' => 'required|string|max:30',
        ]);

        $locale = session('locale', 'en');
        $room = $lobbyService->createRoom($this->nickname, $locale);

        $this->dispatch('redirect', url: "/room/{$room->code}/narrator");
    }

    public function render()
    {
        return view('livewire.lobby.create-room');
    }
}
