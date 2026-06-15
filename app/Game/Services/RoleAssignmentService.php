<?php

namespace App\Game\Services;

use App\Events\GameStarted;
use App\Events\RoleAssigned;
use App\Models\GameState;
use App\Models\Player;
use App\Models\Role;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

class RoleAssignmentService
{
    public function assign(Room $room): GameState
    {
        return DB::transaction(function () use ($room) {
            $this->validate($room);

            $players = $room->players()->where('is_narrator', false)->get();
            $roleComposition = $room->settings['role_composition'] ?? [];

            $pool = $this->buildPool($roleComposition);
            $pool = $this->shufflePool($pool);
            $this->assignRoles($players, $pool);
            $this->assignSeatPositions($players, $room);

            $gameState = $this->createGameState($room, $players);
            $this->sendNotifications($room, $players, $gameState);

            $room->update(['status' => 'playing']);

            broadcast(new GameStarted($room->id));

            return $gameState;
        });
    }

    protected function validate(Room $room): void
    {
        if ($room->status !== 'waiting') {
            throw new \RuntimeException('Room is not in waiting status');
        }

        $players = $room->players()->where('is_narrator', false)->count();
        $totalRoles = array_sum($room->settings['role_composition'] ?? []);

        if ($totalRoles !== $players) {
            throw new \RuntimeException("Role count ({$totalRoles}) does not match player count ({$players})");
        }

        $roleComposition = $room->settings['role_composition'] ?? [];
        $wolfRoles = ['werewolf', 'big_bad_wolf', 'accursed_wolf_father', 'white_werewolf', 'wolf_hound', 'silencer'];
        $hasWolf = false;
        foreach ($wolfRoles as $wolf) {
            if (($roleComposition[$wolf] ?? 0) > 0) {
                $hasWolf = true;
                break;
            }
        }
        if (!$hasWolf) {
            throw new \RuntimeException('At least 1 werewolf faction role required');
        }
    }

    protected function buildPool(array $roleComposition): array
    {
        $pool = [];
        foreach ($roleComposition as $roleKey => $count) {
            for ($i = 0; $i < $count; $i++) {
                $pool[] = $roleKey;
            }
        }
        return $pool;
    }

    protected function shufflePool(array $pool): array
    {
        return collect($pool)->shuffle()->toArray();
    }

    protected function assignRoles($players, array $pool): void
    {
        foreach ($players as $index => $player) {
            $roleKey = $pool[$index];
            $role = Role::where('key', $roleKey)->first();
            $player->update(['role_id' => $role->id]);
        }
    }

    protected function assignSeatPositions($players, Room $room): void
    {
        $seatOrder = $room->settings['seat_order'] ?? [];
        foreach ($players as $player) {
            $position = array_search($player->id, $seatOrder);
            if ($position !== false) {
                $player->update(['seat_position' => $position]);
            }
        }
    }

    protected function createGameState(Room $room, $players): GameState
    {
        $data = GameState::defaultData();
        $data['seat_order'] = $room->settings['seat_order'] ?? [];
        $data['silencer_ability_count'] = $players->count() <= 10 ? 1 : 2;

        return GameState::create([
            'room_id' => $room->id,
            'phase' => 'night',
            'round' => 1,
            'data' => $data,
        ]);
    }

    protected function sendNotifications(Room $room, $players, GameState $gameState): void
    {
        foreach ($players as $player) {
            $role = $player->role;
            broadcast(new RoleAssigned(
                $player->id,
                $role->key,
                $role->faction,
                $role->night_order,
                $role->abilities ?? []
            ));
        }

        $this->notifyTwoSisters($room, $players);
        $this->notifyThreeBrothers($room, $players);
        $this->notifyWerewolfPack($room, $players);
        $this->notifyKira($room, $players);
    }

    protected function notifyTwoSisters(Room $room, $players): void
    {
        $sisters = $players->filter(fn ($p) => $p->role && $p->role->key === 'two_sisters')->values();
        if ($sisters->count() === 2) {
            broadcast(new \App\Events\RoleAssigned(
                $sisters[0]->id,
                'two_sisters_info',
                'village',
                null,
                ['partner_nickname' => $sisters[1]->nickname]
            ));
            broadcast(new \App\Events\RoleAssigned(
                $sisters[1]->id,
                'two_sisters_info',
                'village',
                null,
                ['partner_nickname' => $sisters[0]->nickname]
            ));
        }
    }

    protected function notifyThreeBrothers(Room $room, $players): void
    {
        $brothers = $players->filter(fn ($p) => $p->role && $p->role->key === 'three_brothers')->values();
        if ($brothers->count() === 3) {
            foreach ($brothers as $brother) {
                $others = $brothers->filter(fn ($b) => $b->id !== $brother->id)->pluck('nickname')->toArray();
                broadcast(new \App\Events\RoleAssigned(
                    $brother->id,
                    'three_brothers_info',
                    'village',
                    null,
                    ['brothers_nicknames' => $others]
                ));
            }
        }
    }

    protected function notifyWerewolfPack(Room $room, $players): void
    {
        $wolfRoles = ['werewolf', 'big_bad_wolf', 'accursed_wolf_father', 'white_werewolf', 'silencer'];
        $wolves = $players->filter(fn ($p) => $p->role && in_array($p->role->key, $wolfRoles))->values();

        foreach ($wolves as $wolf) {
            $packmates = $wolves->filter(fn ($w) => $w->id !== $wolf->id)
                ->map(fn ($w) => ['nickname' => $w->nickname, 'role' => $w->role->key])
                ->toArray();
            broadcast(new \App\Events\RoleAssigned(
                $wolf->id,
                'werewolf_pack_info',
                'werewolves',
                null,
                ['packmates' => $packmates]
            ));
        }
    }

    protected function notifyKira(Room $room, $players): void
    {
        $kira = $players->first(fn ($p) => $p->role && $p->role->key === 'kira');
        if ($kira) {
            broadcast(new \App\Events\RoleAssigned(
                $kira->id,
                'kira_identity',
                'neutral',
                null,
                ['nickname' => $kira->nickname]
            ))->toOthers();
        }
    }
}
