<?php

namespace App\Livewire\Lobby;

use App\Game\Services\LobbyService;
use App\Models\Room;
use Livewire\Component;

class JoinRoom extends Component
{
    public string $code = '';
    public string $nickname = '';

    public function mount(?string $code = null)
    {
        $this->code = $code ?? '';
    }

    public function submit(LobbyService $lobbyService)
    {
        $this->validate([
            'code' => 'required|string|size:6',
            'nickname' => 'required|string|max:30',
        ]);

        $room = Room::where('code', $this->code)->first();

        if (!$room) {
            $this->addError('code', __('lobby.errors.room_not_found'));
            return;
        }

        $request = request();
        $lobbyService->joinRoom($room, $this->nickname, $request);

        $this->dispatch('redirect', url: "/room/{$this->code}/player");
    }

    public function render()
    {
        return view('livewire.lobby.join-room')->layout('layouts.app');
    }
}
