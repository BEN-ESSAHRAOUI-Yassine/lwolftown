## Why

Players need a way to create and join game rooms. Without this, no game can start — the lobby is the entry point for all gameplay. This is the first user-facing feature after infrastructure setup.

## What Changes

- Create `LobbyService` with `createRoom()`, `joinRoom()`, and `validateGameStart()` methods
- Create `CreateRoom` Livewire component (nickname input → creates room → redirect to narrator lobby)
- Create `JoinRoom` Livewire component (code + nickname → joins room → redirect to player lobby)
- Create `LobbyController` for API endpoints (POST /api/rooms, POST /api/rooms/join)
- Create `welcome.blade.php` with language toggle and create/join links
- Create `QrHelper` utility for QR code generation
- Add all lobby routes to `web.php`
- Create PlayerJoined event (ShouldBroadcast on room.{id})

## Capabilities

### New Capabilities
- `lobby-service`: LobbyService createRoom, joinRoom, validateGameStart methods
- `lobby-components`: CreateRoom and JoinRoom Livewire components, welcome view, routes

### Modified Capabilities

## Impact

- `app/Game/Services/LobbyService.php` — new service class
- `app/Http/Controllers/LobbyController.php` — new thin controller
- `app/Livewire/Lobby/CreateRoom.php` — new Livewire component
- `app/Livewire/Lobby/JoinRoom.php` — new Livewire component
- `app/Events/PlayerJoined.php` — new ShouldBroadcast event
- `app/Helpers/QrHelper.php` — new helper class
- `resources/views/welcome.blade.php` — new view
- `resources/views/livewire/lobby/create-room.blade.php` — new view
- `resources/views/livewire/lobby/join-room.blade.php` — new view
- `routes/web.php` — add lobby routes
