## Why

The project has a scaffold (Prompt 01) but no database layer. Every game feature — rooms, players, roles, night actions, voting, couple bonds — requires a database schema and Eloquent models. This change establishes the complete data foundation that all subsequent game logic prompts depend on.

## What Changes

- 7 database migrations created in dependency order (rooms → players → roles → game_states → night_actions → votes → couple_bonds)
- 7 Eloquent models with exact fillable arrays, casts, and relationships per scratch.md Section 5
- GameState.data JSON column initialized with all 33 default keys per scratch.md Section 4
- RoleSeeder populating all 27 roles (18 village, 6 werewolf, 3 neutral) with correct keys, factions, night_orders, abilities, and win_conditions
- DatabaseSeeder calling RoleSeeder

## Capabilities

### New Capabilities

- `database-schema`: All 7 migration files, column definitions, foreign keys, indexes, and GameState.data JSON default structure
- `eloquent-models`: 7 Eloquent models (Room, Player, Role, GameState, NightAction, Vote, CoupleBond) with fillable, casts, relationships, and the RoleSeeder/DatabaseSeeder

### Modified Capabilities

<!-- No existing specs modified — Prompt 01 specs remain unchanged. -->

## Impact

- **Files created**: 7 migration files in database/migrations/, 7 models in app/Models/, RoleSeeder and DatabaseSeeder in database/seeders/
- **Dependencies**: None added — uses existing Laravel Eloquent and database features
- **Systems affected**: All game logic (services, actions, events, Livewire components) will depend on these models and schema
- **Database**: SQLite locally, PostgreSQL in production — all migrations must be compatible with both
