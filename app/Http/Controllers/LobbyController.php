<?php

namespace App\Http\Controllers;

use App\Game\Services\LobbyService;
use App\Models\Room;
use Illuminate\Http\Request;

class LobbyController extends Controller
{
    public function __construct(
        protected LobbyService $lobbyService,
    ) {}

    public function create(Request $request)
    {
        $validated = $request->validate([
            'nickname' => 'required|string|max:30',
            'locale' => 'required|in:en,fr',
        ]);

        $room = $this->lobbyService->createRoom(
            $validated['nickname'],
            $validated['locale'],
        );

        return redirect("/room/{$room->code}/narrator");
    }

    public function join(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:6',
            'nickname' => 'required|string|max:30',
        ]);

        $room = Room::where('code', $validated['code'])->firstOrFail();

        $this->lobbyService->joinRoom($room, $validated['nickname'], $request);

        return redirect("/room/{$validated['code']}/player");
    }
}
