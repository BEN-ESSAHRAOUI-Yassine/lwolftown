<div wire:poll.3s class="flex items-center justify-center min-h-screen">
    <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-8 w-full max-w-md text-center">
        <h2 class="font-cinzel text-2xl text-white mb-2">{{ $room->code }}</h2>
        <p class="text-gray-400 text-sm mb-6">waiting for narrator to start the game...</p>

        <div class="space-y-2 mb-6">
            @foreach($players as $player)
                <div class="flex items-center justify-between text-sm py-2 px-3 bg-gray-800/50 rounded">
                    <span class="text-white">{{ $player->nickname }}</span>
                    @if($player->is_narrator)
                        <span class="text-xs text-amber-500">narrator</span>
                    @endif
                </div>
            @endforeach
        </div>

        <p class="text-xs text-gray-500">{{ $players->count() }} player(s) joined</p>
    </div>
</div>
