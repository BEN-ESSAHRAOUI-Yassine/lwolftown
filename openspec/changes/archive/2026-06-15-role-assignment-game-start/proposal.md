## Why

The narrator can configure the lobby (role composition, seat order, night order) but cannot launch a game yet. When the narrator clicks "Start Game", roles must be randomly assigned to players, each player must receive their role privately via WebSocket, and the GameState must be created with all default data. Without this, the game cannot begin.

## What Changes

- New `RoleAssignmentService` class that handles the full game launch sequence in a DB transaction
- New `GameStarted` event broadcast on `room.{id}` when the game begins
- New `RoleAssigned` event broadcast on `player.{id}` for each player with their role details
- Special notifications: Two Sisters receive partner nicknames, Werewolf pack receives packmate list, Kira identity only goes to narrator channel
- `NarratorLobby::startGame()` updated to call `RoleAssignmentService` before redirecting
- Room status transitions from `waiting` to `playing`

## Capabilities

### New Capabilities

- `role-assignment`: Core service that shuffles roles from the pool, assigns one per non-narrator player, sets seat positions, creates GameState with full default data, and broadcasts all assignment events

### Modified Capabilities

- `narrator-lobby`: Start Game button now calls RoleAssignmentService before redirect (implementation change, no spec-level behavior change needed)

## Impact

- New file: `app/Game/Services/RoleAssignmentService.php`
- New file: `app/Events/GameStarted.php`
- New file: `app/Events/RoleAssigned.php`
- Modified: `app/Livewire/Narrator/NarratorLobby.php` (startGame method)
- All 27 role types must be assigned correctly
- Event channels: `room.{id}` for GameStarted, `player.{id}` for RoleAssigned, `narrator.{room_id}` for Kira identity
