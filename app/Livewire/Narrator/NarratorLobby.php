<?php

namespace App\Livewire\Narrator;

use App\Events\PlayerLeft;
use App\Game\Services\LobbyService;
use App\Helpers\QrHelper;
use App\Models\Player;
use App\Models\Role;
use App\Models\Room;
use Livewire\Component;

class NarratorLobby extends Component
{
    public Room $room;
    public $players;
    public array $roleComposition = [];
    public array $nightOrder = [];
    public array $seatOrder = [];
    public array $difficultySettings = [];
    public array $disclosureSettings = [];
    public array $presets = [];
    public array $validationErrors = [];
    public array $allRoles = [];
    public string $newPresetName = '';

    public $pollInterval = 3000;

    protected $listeners = [
        'playerJoined' => '$refresh',
    ];

    public function mount(Room $room): void
    {
        $this->room = $room;
        $this->loadPlayers();
        $this->loadRoles();
        $this->initSettings();
    }

    public function loadPlayers(): void
    {
        $this->players = $this->room->players()->orderBy('created_at')->get();
    }

    protected function loadRoles(): void
    {
        $roles = Role::orderBy('faction')->orderBy('night_order')->get();
        $this->allRoles = $roles->groupBy('faction')->toArray();
    }

    protected function initSettings(): void
    {
        $settings = $this->room->settings ?? [];

        $this->roleComposition = $settings['role_composition'] ?? array_fill_keys(
            array_column(Role::all()->toArray(), 'key'),
            0
        );

        $this->nightOrder = $settings['night_order'] ?? $this->getDefaultNightOrder();
        $this->seatOrder = $settings['seat_order'] ?? $this->players->pluck('id')->toArray();
        $this->difficultySettings = $settings['difficulty_settings'] ?? [
            'night_mode' => 'narrator_driven',
            'silencer_vote_ban' => false,
            'bear_tamer_public' => true,
            'kira_unknown_death' => true,
        ];
        $this->disclosureSettings = $settings['disclosure_settings'] ?? [
            'village' => true,
            'werewolves' => true,
            'neutral' => true,
        ];
        $this->presets = $settings['presets'] ?? [];
    }

    protected function getDefaultNightOrder(): array
    {
        return [
            ['key' => 'cupid', 'order' => 0],
            ['key' => 'the_master', 'order' => 1],
            ['key' => 'silencer', 'order' => 2],
            ['key' => 'wolf_hound', 'order' => 3],
            ['key' => 'accursed_wolf_father', 'order' => 4],
            ['key' => 'werewolf', 'order' => 5],
            ['key' => 'big_bad_wolf', 'order' => 6],
            ['key' => 'white_werewolf', 'order' => 7],
            ['key' => 'bodyguard', 'order' => 8],
            ['key' => 'little_girl', 'order' => 9],
            ['key' => 'seer', 'order' => 10],
            ['key' => 'witch', 'order' => 11],
            ['key' => 'pied_piper', 'order' => 12],
            ['key' => 'fox', 'order' => 13],
            ['key' => 'bear_tamer', 'order' => 14],
            ['key' => 'kira', 'order' => 15],
        ];
    }

    public function incrementRole(string $roleKey): void
    {
        $this->roleComposition[$roleKey] = ($this->roleComposition[$roleKey] ?? 0) + 1;
        $this->saveSettings();
        $this->validateRoleComposition();
    }

    public function decrementRole(string $roleKey): void
    {
        if (($this->roleComposition[$roleKey] ?? 0) > 0) {
            $this->roleComposition[$roleKey]--;
            $this->saveSettings();
            $this->validateRoleComposition();
        }
    }

    public function validateRoleComposition(): void
    {
        $this->validationErrors = [];
        $playerCount = $this->players->where('is_narrator', false)->count();
        $totalRoles = array_sum($this->roleComposition);

        if ($totalRoles !== $playerCount) {
            $this->validationErrors[] = 'Total roles ('.$totalRoles.') must equal player count ('.$playerCount.')';
        }

        if (($this->roleComposition['two_sisters'] ?? 0) === 1) {
            $this->validationErrors[] = 'Two Sisters must be exactly 0 or 2';
        }

        $brothers = $this->roleComposition['three_brothers'] ?? 0;
        if ($brothers > 0 && $brothers !== 3) {
            $this->validationErrors[] = 'Three Brothers must be exactly 0 or 3';
        }

        $soloRoles = ['cupid', 'kira', 'angel', 'pied_piper', 'the_master', 'elder', 'scapegoat', 'village_idiot', 'knight_with_rusty_sword', 'devoted_servant'];
        foreach ($soloRoles as $solo) {
            if (($this->roleComposition[$solo] ?? 0) > 1) {
                $this->validationErrors[] = ucfirst(str_replace('_', ' ', $solo)).' maximum 1 each';
            }
        }

        $wolfRoles = ['werewolf', 'big_bad_wolf', 'accursed_wolf_father', 'white_werewolf', 'wolf_hound', 'silencer'];
        $hasWolf = false;
        foreach ($wolfRoles as $wolf) {
            if (($this->roleComposition[$wolf] ?? 0) > 0) {
                $hasWolf = true;
                break;
            }
        }
        if (!$hasWolf && $totalRoles > 0) {
            $this->validationErrors[] = 'At least 1 werewolf faction role required';
        }
    }

    public function canStartGame(): bool
    {
        $this->validateRoleComposition();
        return empty($this->validationErrors);
    }

    public function kickPlayer(int $playerId): void
    {
        $player = Player::find($playerId);
        if ($player && $player->room_id === $this->room->id) {
            $playerCount = $this->room->players()->where('id', '!=', $playerId)->count();
            broadcast(new PlayerLeft($this->room->id, $playerId, $playerCount))->toOthers();
            $player->delete();
            $this->loadPlayers();
            $this->seatOrder = array_values(array_filter($this->seatOrder, fn($id) => $id !== $playerId));
            $this->saveSettings();
        }
    }

    public function reorderSeatOrder(array $order): void
    {
        $this->seatOrder = $order;
        $this->saveSettings();
    }

    public function reorderNightOrder(array $order): void
    {
        $this->nightOrder = $order;
        $this->saveSettings();
    }

    public function resetNightOrder(): void
    {
        $this->nightOrder = $this->getDefaultNightOrder();
        $this->saveSettings();
    }

    public function toggleDifficulty(string $key): void
    {
        $this->difficultySettings[$key] = !$this->difficultySettings[$key];
        $this->saveSettings();
    }

    public function setNightMode(string $mode): void
    {
        $this->difficultySettings['night_mode'] = $mode;
        $this->saveSettings();
    }

    public function toggleDisclosure(string $faction): void
    {
        $this->disclosureSettings[$faction] = !$this->disclosureSettings[$faction];
        $this->saveSettings();
    }

    public function savePreset(): void
    {
        if (!empty($this->newPresetName)) {
            $this->presets[] = [
                'name' => $this->newPresetName,
                'role_composition' => $this->roleComposition,
            ];
            $this->newPresetName = '';
            $this->saveSettings();
        }
    }

    public function loadPreset(int $index): void
    {
        if (isset($this->presets[$index])) {
            $this->roleComposition = $this->presets[$index]['role_composition'];
            $this->saveSettings();
            $this->validateRoleComposition();
        }
    }

    public function deletePreset(int $index): void
    {
        unset($this->presets[$index]);
        $this->presets = array_values($this->presets);
        $this->saveSettings();
    }

    protected function saveSettings(): void
    {
        $this->room->update([
            'settings' => [
                'role_composition' => $this->roleComposition,
                'night_order' => $this->nightOrder,
                'seat_order' => $this->seatOrder,
                'difficulty_settings' => $this->difficultySettings,
                'disclosure_settings' => $this->disclosureSettings,
                'presets' => $this->presets,
            ],
        ]);
    }

    public function startGame()
    {
        if (!$this->canStartGame()) {
            return;
        }

        $this->saveSettings();

        return $this->redirect("/game/{$this->room->code}/narrator");
    }

    public function getQrCode(): string
    {
        return QrHelper::generate(config('app.url').'/join/'.$this->room->code);
    }

    public function render()
    {
        return view('livewire.narrator.narrator-lobby');
    }
}
