## Context

The app needs a lobby system where players create rooms and join them before a game starts. Currently only infrastructure exists (models, migrations, auth). No user-facing features are built yet.

Constraints:
- No user accounts — identity is session_token cookie
- Room codes are 6-char uppercase alphanumeric
- Max 24 players per room
- Host is automatically narrator
- All strings via `__('key')` for EN/FR localization

## Goals / Non-Goals

**Goals:**
- LobbyService handles all room creation and joining logic
- Livewire components for create/join flows
- Thin controllers for API endpoints
- Welcome page with language toggle
- PlayerJoined event broadcasts to room.{id}

**Non-Goals:**
- NarratorLobby (Prompt 05)
- Role assignment or game start (Prompt 06)
- QR code display in lobby (Prompt 05 handles this)
- Validation error translations (locale files in Prompt 18)

## Decisions

### Room code generation
Use `Str::random(5)` filtered to uppercase A-Z + digits, then check uniqueness. 6 chars gives 2 billion+ combinations — collision is negligible.

### Livewire vs controller-only
Livewire for CreateRoom/JoinRoom (form handling, validation, JS redirect). Thin LobbyController for API endpoints only (POST /api/rooms, POST /api/rooms/join).

### Session storage
- `session_token` stored in httpOnly cookie (not session) — persists across requests
- `locale` stored in session — used by AppServiceProvider boot()

### Event broadcasting
PlayerJoined fires on room.{id} channel. Payload: player{id, nickname, is_narrator}, player_count. Used by NarratorLobby to update player list.

## Risks / Trade-offs

- **[Risk]** Race condition on room code generation → use unique index + retry on collision
- **[Risk]** Duplicate nickname in same room → validated in joinRoom, returns error
- **[Trade-off]** QrHelper is a static utility class vs injected service → static is simpler, QR generation is stateless
