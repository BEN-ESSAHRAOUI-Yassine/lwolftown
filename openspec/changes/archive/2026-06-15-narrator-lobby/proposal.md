## Why

After players create and join rooms (Prompt 04), the narrator needs a control panel to configure the game before starting. This is where role composition, seat order, night order, difficulty settings, and presets are managed. Without this, no game can begin.

## What Changes

- Create NarratorLobby Livewire component with all lobby configuration panels
- QR code display for player joining
- Live player list with polling (3s)
- Drag-and-drop seat order
- Role composition panel with +/- counters and live validation
- Night order drag-and-drop reorderable list
- Difficulty settings toggles
- Information disclosure toggles
- Preset save/load system
- Player kick functionality
- [Start Game] button with validation gate

## Capabilities

### New Capabilities
- `narrator-lobby`: NarratorLobby Livewire component, QR display, player list, seat order, role composition, night order, difficulty settings, presets, start game flow

### Modified Capabilities

## Impact

- `app/Livewire/Narrator/NarratorLobby.php` — new Livewire component
- `resources/views/livewire/narrator/narrator-lobby.blade.php` — new view
- `app/Events/PlayerLeft.php` — new event for kick functionality
- `routes/web.php` — add GET /room/{room}/narrator route
- Depends on: LobbyService (Prompt 04), QrHelper (Prompt 04), PlayerJoined event (Prompt 04)
