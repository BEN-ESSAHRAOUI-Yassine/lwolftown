## Context

The Loup-Garou companion app has a narrator lobby where the narrator configures role composition, seat order, night order, and difficulty settings. The "Start Game" button exists but doesn't actually launch the game — it just redirects. We need the backend service that transforms lobby configuration into an active game state.

Current state:
- Room model exists with `status: 'waiting'` and `settings` JSON containing role_composition, seat_order, night_order
- Player model has `role_id`, `seat_position`, `is_alive` fields
- GameState model has `defaultData()` method with all 38 keys
- Role model has `key`, `faction`, `night_order`, `abilities` fields
- 27 roles seeded via RoleSeeder
- NarratorLobby component has `startGame()` method that currently just redirects

## Goals / Non-Goals

**Goals:**
- Randomly assign roles from the configured pool to non-narrator players
- Create GameState with phase='night', round=1, and full default data
- Broadcast RoleAssigned to each player privately
- Handle special role notifications (Sisters, Brothers, Wolves, Kira)
- Transition room status from 'waiting' to 'playing'

**Non-Goals:**
- Game engine logic (PhaseManager, ActionResolver, etc.) — that's Prompt 07
- Night action handling — separate prompt
- Voting system — separate prompt
- Role-specific abilities at assignment time — roles get their data, abilities happen during night phases

## Decisions

### 1. Single service method in a DB transaction

**Decision:** `RoleAssignmentService::assign(Room)` performs everything in one DB transaction.

**Why:** Role assignment, GameState creation, and room status update must be atomic. If any step fails, the entire operation rolls back. No partial game states.

**Alternatives considered:**
- Multiple methods called sequentially: Risk of partial state if中间步骤 fails
- Event-driven approach (fire event, listener does assignment): Adds complexity for a synchronous operation that needs immediate results

### 2. Build role pool from settings, not from database counts

**Decision:** Read `room->settings['role_composition']` to build the role pool (e.g., `['seer' => 1, 'werewolf' => 2, 'villager' => 5]`), then look up Role models by key.

**Why:** The narrator explicitly configured the composition. We must respect exactly what they set, not query the database for "how many of each role exist."

### 3. Shuffle pool with Collection::shuffle()

**Decision:** Use Laravel's `Collection::shuffle()` for randomization.

**Why:** Built-in, simple, sufficient. No need for cryptographically secure randomness in a party game.

### 4. Special notifications via separate broadcast calls

**Decision:** After assigning all roles, make separate broadcast calls for special role groups:
- Two Sisters: each gets partner's nickname
- Three Brothers: each gets both brothers' nicknames
- Werewolf pack: each wolf gets list of packmates
- Kira: identity sent only to narrator channel

**Why:** Each special notification has different data and targets. Keeping them separate is clearer than a complex unified notification system.

### 5. silencer_ability_count based on player count

**Decision:** Set `silencer_ability_count` to 1 if non-narrator player count ≤ 10, else 2.

**Why:** Per scratch.md spec. Balances the silencer's power in larger games.

## Risks / Trade-offs

**[Risk] Race condition if narrator clicks Start Game twice quickly** → Mitigation: Check room status at start of assign(), throw exception if not 'waiting'. The DB transaction ensures only one assignment succeeds.

**[Risk] Role pool doesn't match player count** → Mitigation: NarratorLobby already validates this before enabling Start Game button. RoleAssignmentService should also validate and throw if mismatch detected.

**[Risk] Special notifications sent before player clients are listening** → Mitigation: Players are already on the PlayerLobby page which listens to player.{id} channels. The events are persistent (ShouldBroadcast), so they'll be received when the client connects.

**[Risk] Kira identity leaked to players** → Mitigation: Kira notification uses `narrator.{room_id}` channel, which only the narrator subscribes to. Verify channel auth rules in channels.php.
