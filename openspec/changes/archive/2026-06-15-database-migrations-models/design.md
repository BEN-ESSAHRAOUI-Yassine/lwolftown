## Context

The project has a Laravel 13.7 scaffold with TailwindCSS, Livewire, Reverb, and all frontend tooling configured (Prompt 01). However, there is no database layer — no migrations, no models, no seeders. Every game feature (rooms, players, roles, night actions, voting, couple bonds) depends on a database schema. This change builds the complete data foundation.

## Goals / Non-Goals

**Goals:**
- 7 migrations with correct columns, types, defaults, and foreign keys
- 7 Eloquent models with exact fillable, casts, and relationships from scratch.md Section 5
- GameState.data JSON column with all 33 default keys initialized correctly
- RoleSeeder populating all 27 roles with exact data from scratch.md Section 6
- Compatible with both SQLite (local) and PostgreSQL (production)

**Non-Goals:**
- Game logic, services, or actions (Prompt 08+)
- Role assignment logic (Prompt 06)
- Event broadcasting (Prompt 16)
- UI or Livewire components (Prompt 05+)

## Decisions

### D1: Migration order follows foreign key dependencies
**Choice:** rooms → players → roles → game_states → night_actions → votes → couple_bonds
**Why:** players depends on rooms and roles; game_states depends on rooms; night_actions/votes depend on game_states and players; couple_bonds depends on game_states and players.
**Alternative:** None — this is the only valid dependency order.

### D2: GameState room_id unique constraint
**Choice:** Add unique constraint on game_states.room_id
**Why:** scratch.md Section 4 specifies `room_id FK->rooms UNIQUE`. One room can only have one game state at a time.
**Alternative:** None — specified in source.

### D3: Player session_token unique constraint
**Choice:** Add unique constraint on players.session_token
**Why:** Used for authentication via cookie. Must be unique to resolve a single player.
**Alternative:** None — specified in source.

### D4: GameState.data as JSON with array cast
**Choice:** Cast data column as array, initialize with all 33 default keys on GameState creation.
**Why:** scratch.md Section 4 lists all JSON keys with defaults. Casting as array allows direct PHP access without manual json_decode.
**Alternative:** Store as raw JSON and decode per access — slower, more error-prone.

### D5: RoleSeeder uses updateOrCreate on key
**Choice:** Seed roles using `updateOrCreate` on the `key` field.
**Why:** Idempotent — can run multiple times without duplicates. Safe for development and production.
**Alternative:** `insert` — fails on duplicate key. `delete + insert` — loses data.

### D6: Boolean defaults
**Choice:** All boolean fields default to false except `is_alive` which defaults to true.
**Why:** Players are alive by default when created. All other flags (is_host, is_narrator, voting_banned, is_silenced, is_slave) start as false.
**Alternative:** None — specified in scratch.md.

### D7: Role abilities stored as JSON
**Choice:** Cast abilities column as JSON array.
**Why:** scratch.md Section 4 specifies `abilities JSON NULLABLE`. Each role has different ability configurations.
**Alternative:** Separate abilities table — over-normalized for this use case.

## Risks / Trade-offs

- **[Risk] SQLite vs PostgreSQL compatibility** → Use only standard SQL types (bigint, varchar, boolean, text, timestamp, json). No PostgreSQL-specific types. Mitigation: Laravel migrations handle platform differences.
- **[Risk] GameState.data default keys drift** → If scratch.md adds/removes keys, the seeder must update. Mitigation: Centralize default data in GameState model or a constant.
- **[Risk] RoleSeeder data accuracy** → 27 roles with specific night_orders, abilities, win_conditions. Mitigation: Data comes directly from scratch.md Section 6 — single source of truth.
- **[Trade-off] No soft deletes** → Deleted records are gone. Acceptable for a game app with session-based identity, not user accounts.
