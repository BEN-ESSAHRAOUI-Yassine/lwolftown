## Context

The narrator lobby is the pre-game configuration screen where the host sets up role composition, seat order, night order, and difficulty settings before starting the game. It's a single Livewire component with multiple sub-panels.

Current state:
- Players can create/join rooms (Prompt 04)
- Room model has settings JSON column for storing configuration
- RoleSeeder has all 27 roles with descriptions
- QrHelper generates SVG QR codes

## Goals / Non-Goals

**Goals:**
- NarratorLobby component with all configuration panels
- QR code + live player list (3s polling)
- Seat order drag-and-drop
- Role composition with validation
- Night order drag-and-drop
- Difficulty settings
- Preset save/load
- Start game validation gate

**Non-Goals:**
- Game state creation (Prompt 06 handles RoleAssignmentService)
- Player game views (Prompt 05 only covers narrator lobby)
- Actual role assignment logic

## Decisions

### Storage: Room.settings JSON
All configuration (role_composition, night_order, seat_order, difficulty_settings, presets) stored in Room.settings JSON column. Avoids extra tables for transient game config.

### Polling vs events for player list
Player list uses 3s polling (Livewire `poll(3s)`) rather than WebSocket events. Reason: player list updates are infrequent (join/kick), polling is simpler, and narrator is the only viewer.

### Seat order: circular array
Seat order stored as array of player_ids in circular order. Visual preview uses CSS for circular layout. Locked permanently on game start.

### Role composition validation
Hard validations enforced server-side in Livewire component:
- Two Sisters: 0 or 2
- Three Brothers: 0 or 3
- Solo roles: max 1
- Total roles = player count (excluding narrator)

### Presets stored in Room.settings
Presets saved to room->settings['presets'] array. Each preset has name + role_composition. Auto-suggest on player count detection.

## Risks / Trade-offs

- **[Risk]** Large Livewire component → split into sub-components if needed, but keep single file for simplicity initially
- **[Risk]** Drag-and-drop requires Alpine.js interactivity → use Sortable.js via CDN or Livewire sortable plugin
- **[Trade-off]** Polling vs WebSocket → polling is simpler but adds 3s latency for player list updates. Acceptable for lobby phase.
