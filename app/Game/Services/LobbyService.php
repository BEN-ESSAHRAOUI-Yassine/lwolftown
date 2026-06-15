<?php

namespace App\Game\Services;

use App\Events\PlayerJoined;
use App\Models\Player;
use App\Models\Role;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LobbyService
{
    public function createRoom(string $nickname, string $locale): Room
    {
        $code = $this->generateUniqueCode();

        $room = Room::create([
            'code' => $code,
            'status' => 'waiting',
        ]);

        $sessionToken = Str::uuid()->toString();

        $player = Player::create([
            'room_id' => $room->id,
            'nickname' => $nickname,
            'session_token' => $sessionToken,
            'is_narrator' => true,
            'is_host' => true,
        ]);

        session(['locale' => $locale]);

        cookie()->queue(
            cookie('session_token', $sessionToken, 120, '/', null, false, true)
        );

        return $room;
    }

    public function joinRoom(Room $room, string $nickname, Request $request): Player
    {
        if ($room->status !== 'waiting') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'code' => __('lobby.errors.room_not_waiting'),
            ]);
        }

        if ($room->players()->where('nickname', $nickname)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'nickname' => __('lobby.errors.nickname_taken'),
            ]);
        }

        if ($room->players()->count() >= 24) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'code' => __('lobby.errors.room_full'),
            ]);
        }

        $sessionToken = Str::uuid()->toString();

        $player = Player::create([
            'room_id' => $room->id,
            'nickname' => $nickname,
            'session_token' => $sessionToken,
            'is_narrator' => false,
        ]);

        cookie()->queue(
            cookie('session_token', $sessionToken, 120, '/', null, false, true)
        );

        broadcast(new PlayerJoined($player, $room->players()->count()))->toOthers();

        return $player;
    }

    public function validateGameStart(Room $room): array
    {
        $errors = [];
        $nonNarratorPlayers = $room->players()->where('is_narrator', false)->get();
        $playerCount = $nonNarratorPlayers->count();

        if ($playerCount < 4) {
            $errors[] = 'not_enough_players';
        }

        $roleComposition = $room->settings['role_composition'] ?? [];
        $totalRoles = array_sum($roleComposition);

        if ($totalRoles !== $playerCount) {
            $errors[] = 'role_count_mismatch';
        }

        $wolfRoles = ['werewolf', 'big_bad_wolf', 'accursed_wolf_father', 'white_werewolf', 'wolf_hound', 'silencer'];
        $hasWerewolf = false;
        foreach ($wolfRoles as $wolfRole) {
            if (($roleComposition[$wolfRole] ?? 0) > 0) {
                $hasWerewolf = true;
                break;
            }
        }
        if (!$hasWerewolf) {
            $errors[] = 'no_werewolves';
        }

        $twoSistersCount = $roleComposition['two_sisters'] ?? 0;
        if ($twoSistersCount !== 0 && $twoSistersCount !== 2) {
            $errors[] = 'two_sisters_count';
        }

        $threeBrothersCount = $roleComposition['three_brothers'] ?? 0;
        if ($threeBrothersCount !== 0 && $threeBrothersCount !== 3) {
            $errors[] = 'three_brothers_count';
        }

        $soloRoles = ['cupid', 'kira', 'angel', 'pied_piper', 'the_master', 'elder', 'scapegoat', 'village_idiot', 'knight_with_rusty_sword', 'devoted_servant'];
        foreach ($soloRoles as $soloRole) {
            if (($roleComposition[$soloRole] ?? 0) > 1) {
                $errors[] = 'solo_role_duplicate';
                break;
            }
        }

        return $errors;
    }

    protected function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(5));
        } while (Room::where('code', $code)->exists());

        return $code;
    }
}
