## 1. Migrations

- [x] 1.1 Create create_rooms_table migration (code varchar 6 unique, host_player_id FK nullable, status, narration_mode, night_mode, settings JSON)
- [x] 1.2 Create create_players_table migration (room_id FK, nickname, session_token unique, role_id FK nullable, booleans, master_id FK nullable, seat_position)
- [x] 1.3 Create create_roles_table migration (key unique, faction, night_order int nullable, abilities JSON nullable, win_condition)
- [x] 1.4 Create create_game_states_table migration (room_id FK unique, phase, round int, data JSON nullable)
- [x] 1.5 Create create_night_actions_table migration (game_state_id FK, player_id FK, action_type, target_id FK nullable, metadata JSON nullable, resolved_at timestamp nullable)
- [x] 1.6 Create create_votes_table migration (game_state_id FK, voter_id FK, target_id FK, round_type)
- [x] 1.7 Create create_couple_bonds_table migration (game_state_id FK, player_id FK, partner_id FK)

## 2. Eloquent Models

- [x] 2.1 Create Room model (fillable, casts settings->array, routeKey=code, relationships: players, host, gameState)
- [x] 2.2 Create Player model (fillable, boolean casts, relationships: room, role, nightActions, votes, coupleBond, master, slaves)
- [x] 2.3 Create Role model (fillable, casts night_order->integer, abilities->array, relationships: players)
- [x] 2.4 Create GameState model (fillable, casts round->integer, data->array, default data JSON initialization, relationships: room, nightActions, votes, coupleBonds)
- [x] 2.5 Create NightAction model (fillable, casts metadata->array, resolved_at->datetime)
- [x] 2.6 Create Vote model (fillable, relationships: gameState, voter, target)
- [x] 2.7 Create CoupleBond model (fillable)

## 3. Seeders

- [x] 3.1 Create RoleSeeder with all 27 roles using updateOrCreate on key (18 village, 6 werewolf, 3 neutral from scratch.md Section 6)
- [x] 3.2 Update DatabaseSeeder to call RoleSeeder

## 4. Verification

- [x] 4.1 Run `php artisan migrate:fresh` — all 7 migrations run clean
- [x] 4.2 Run `php artisan db:seed` — 27 roles seeded correctly
- [x] 4.3 Run `php artisan db:seed` again — no duplicates (idempotent)
- [x] 4.4 Verify GameState default data contains all 33 keys (38 actual)
- [x] 4.5 Verify Room model route key is 'code'
