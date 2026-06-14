<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('player.{playerId}', fn ($user, $playerId) => $user && $user->id === (int) $playerId);

Broadcast::channel('narrator.{roomId}', fn ($user, $roomId) => $user && $user->room_id === (int) $roomId && $user->is_narrator);

Broadcast::channel('werewolves.{roomId}', fn ($user, $roomId) => $user && $user->room_id === (int) $roomId && $user->role && $user->role->faction === 'werewolves');

Broadcast::channel('room.{roomId}', fn ($user, $roomId) => $user && $user->room_id === (int) $roomId);
