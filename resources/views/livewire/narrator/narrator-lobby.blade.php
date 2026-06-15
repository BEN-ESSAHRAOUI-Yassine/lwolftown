<div wire:poll.3s class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="font-cinzel text-2xl font-bold text-white">
            {{ $room->code }}
        </h1>
        <div class="flex items-center gap-2 text-sm text-gray-400">
            <span>{{ $players->count() }}</span> players joined
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        {{-- Left Panel: QR + Players + Seat Order --}}
        <div class="col-span-4 space-y-6">
            <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-6">
                <div class="text-center">
                    <div class="inline-block bg-white p-2 rounded-lg mb-3">
                        {!! $this->getQrCode() !!}
                    </div>
                    <p class="font-cinzel text-lg text-white">{{ $room->code }}</p>
                </div>
            </div>

            <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                <h3 class="font-cinzel text-sm text-gray-300 mb-3">Players</h3>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @foreach($players as $player)
                        <div class="flex items-center justify-between text-sm py-1 border-b border-gray-800 last:border-0">
                            <div>
                                <span class="text-white">{{ $player->nickname }}</span>
                                @if($player->is_narrator)
                                    <span class="text-xs text-amber-500 ml-1">(narrator)</span>
                                @endif
                            </div>
                            @if(!$player->is_narrator)
                                <button wire:click="kickPlayer({{ $player->id }})"
                                    class="text-xs text-red-500 hover:text-red-400">kick</button>
                            @endif
                        </div>
                    @endforeach
                    @if($players->isEmpty())
                        <p class="text-xs text-gray-500 text-center">no players yet</p>
                    @endif
                </div>
            </div>

            <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                <h3 class="font-cinzel text-sm text-gray-300 mb-3">Seat Order</h3>
                <p class="text-xs text-gray-500 mb-2">Drag to reorder</p>
                <div class="space-y-1">
                    @foreach($seatOrder as $seatId)
                        @php $seatPlayer = $players->firstWhere('id', $seatId); @endphp
                        @if($seatPlayer)
                            <div class="flex items-center gap-2 text-sm px-2 py-1 bg-gray-800/50 rounded cursor-move"
                                wire:sortable-item="{{ $seatId }}"
                                wire:sortable-group="seatOrder">
                                <span class="text-gray-500 text-xs">{{ $loop->index + 1 }}</span>
                                <span class="text-white">{{ $seatPlayer->nickname }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right Panel: Roles + Night Order + Settings --}}
        <div class="col-span-8 space-y-6">
            {{-- Role Composition --}}
            <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-cinzel text-sm text-gray-300">Role Composition</h3>
                    <div class="flex items-center gap-2">
                        <select wire:model="newPresetName" class="bg-gray-800 border border-gray-600 text-white text-xs px-2 py-1 rounded">
                            <option value="">load preset...</option>
                            @foreach($presets as $idx => $preset)
                                <option value="{{ $idx }}">{{ $preset['name'] }}</option>
                            @endforeach
                        </select>
                        <button wire:click="savePreset" class="text-xs text-blue-500 hover:text-blue-400">save preset</button>
                    </div>
                </div>

                @if(!empty($validationErrors))
                    <div class="mb-4 p-3 bg-red-900/30 border border-red-700 rounded text-xs text-red-300 space-y-1">
                        @foreach($validationErrors as $error)
                            <div>• {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                @foreach($allRoles as $faction => $roles)
                    <div class="mb-4">
                        <h4 class="font-cinzel text-xs text-gray-400 mb-2 uppercase tracking-wider">{{ $faction }}</h4>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach($roles as $role)
                                <div class="flex items-center justify-between bg-gray-800/50 rounded px-2 py-1.5 text-xs group">
                                    <div class="flex-1 min-w-0">
                                        <span class="text-white truncate block">{{ __($role['key'].'.name') }}</span>
                                        <span class="text-gray-500 text-[10px] truncate block">{{ __($role['key'].'.description') }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 ml-2">
                                        <button wire:click="decrementRole('{{ $role['key'] }}')"
                                            class="w-5 h-5 rounded bg-gray-700 hover:bg-gray-600 text-white text-xs leading-none">-</button>
                                        <span class="w-4 text-center text-white">{{ $roleComposition[$role['key']] ?? 0 }}</span>
                                        <button wire:click="incrementRole('{{ $role['key'] }}')"
                                            class="w-5 h-5 rounded bg-gray-700 hover:bg-gray-600 text-white text-xs leading-none">+</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Night Order --}}
            <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-cinzel text-sm text-gray-300">Night Order</h3>
                    <button wire:click="resetNightOrder" class="text-xs text-gray-500 hover:text-gray-300">reset</button>
                </div>
                <div class="space-y-1" wire:sortable="reorderNightOrder" wire:sortable-group="nightOrder">
                    @foreach($nightOrder as $idx => $nightRole)
                        <div class="flex items-center gap-2 text-sm px-2 py-1 bg-gray-800/50 rounded cursor-move"
                            wire:sortable-item="{{ $nightRole['key'] }}">
                            <span class="text-gray-500 text-xs w-4">{{ $idx + 1 }}</span>
                            <span class="text-white">{{ __($nightRole['key'].'.name') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Difficulty & Disclosure --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                    <h3 class="font-cinzel text-sm text-gray-300 mb-3">Difficulty</h3>
                    <div class="space-y-2">
                        <label class="flex items-center justify-between text-xs">
                            <span class="text-gray-400">Night Mode</span>
                            <div class="flex gap-1">
                                <button wire:click="setNightMode('narrator_driven')"
                                    class="px-2 py-0.5 rounded text-[10px] {{ $difficultySettings['night_mode'] === 'narrator_driven' ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-400' }}">narrator</button>
                                <button wire:click="setNightMode('player_driven')"
                                    class="px-2 py-0.5 rounded text-[10px] {{ $difficultySettings['night_mode'] === 'player_driven' ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-400' }}">player</button>
                            </div>
                        </label>
                        <label class="flex items-center justify-between text-xs">
                            <span class="text-gray-400">Silencer vote ban</span>
                            <button wire:click="toggleDifficulty('silencer_vote_ban')"
                                class="w-8 h-4 rounded-full {{ $difficultySettings['silencer_vote_ban'] ? 'bg-blue-600' : 'bg-gray-700' }} relative transition">
                                <span class="absolute top-0.5 w-3 h-3 rounded-full bg-white transition {{ $difficultySettings['silencer_vote_ban'] ? 'left-4' : 'left-0.5' }}"></span>
                            </button>
                        </label>
                        <label class="flex items-center justify-between text-xs">
                            <span class="text-gray-400">Bear Tamer public</span>
                            <button wire:click="toggleDifficulty('bear_tamer_public')"
                                class="w-8 h-4 rounded-full {{ $difficultySettings['bear_tamer_public'] ? 'bg-blue-600' : 'bg-gray-700' }} relative transition">
                                <span class="absolute top-0.5 w-3 h-3 rounded-full bg-white transition {{ $difficultySettings['bear_tamer_public'] ? 'left-4' : 'left-0.5' }}"></span>
                            </button>
                        </label>
                        <label class="flex items-center justify-between text-xs">
                            <span class="text-gray-400">Kira unknown death</span>
                            <button wire:click="toggleDifficulty('kira_unknown_death')"
                                class="w-8 h-4 rounded-full {{ $difficultySettings['kira_unknown_death'] ? 'bg-blue-600' : 'bg-gray-700' }} relative transition">
                                <span class="absolute top-0.5 w-3 h-3 rounded-full bg-white transition {{ $difficultySettings['kira_unknown_death'] ? 'left-4' : 'left-0.5' }}"></span>
                            </button>
                        </label>
                    </div>
                </div>

                <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4">
                    <h3 class="font-cinzel text-sm text-gray-300 mb-3">Disclosures</h3>
                    <div class="space-y-2">
                        @foreach(['village', 'werewolves', 'neutral'] as $faction)
                            <label class="flex items-center justify-between text-xs">
                                <span class="text-gray-400">{{ ucfirst($faction) }}</span>
                                <button wire:click="toggleDisclosure('{{ $faction }}')"
                                    class="w-8 h-4 rounded-full {{ $disclosureSettings[$faction] ? 'bg-blue-600' : 'bg-gray-700' }} relative transition">
                                    <span class="absolute top-0.5 w-3 h-3 rounded-full bg-white transition {{ $disclosureSettings[$faction] ? 'left-4' : 'left-0.5' }}"></span>
                                </button>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Start Button --}}
            <div class="flex justify-end">
                <button wire:click="startGame"
                    @disabled(!$this->canStartGame())
                    class="px-8 py-3 rounded-lg font-cinzel text-sm font-bold transition
                        {{ $this->canStartGame() ? 'bg-emerald-600 hover:bg-emerald-500 text-white cursor-pointer' : 'bg-gray-700 text-gray-500 cursor-not-allowed' }}">
                    start game
                </button>
            </div>
        </div>
    </div>
</div>
