# lwolftown — OpenSpec Feature Prompts

> These prompts are structured for use with `/opsx:new` in OpenSpec.
> Each prompt is self-contained and references scratch.md as the source of truth.
> Feed them one at a time, in order. Do not skip sections.
> Every prompt assumes the previous one has been completed and merged.

---

## PROMPT 01 — Project Scaffold & Railway Config

```
Read scratch.md sections 2, 3, 30, 31, 32, 33, 34 carefully before starting.

Scaffold a fresh Laravel 13.7 / PHP 8.3 project with the following setup:

DEPENDENCIES:
- composer require livewire/livewire laravel/reverb chillerlan/php-qrcode
- npm install tailwindcss @tailwindcss/vite laravel-echo pusher-js axios

CONFIGURATION FILES:
1. vite.config.js — exactly as in scratch.md Section 29
2. resources/css/app.css — exactly as in scratch.md Section 31 (full theme, all animations, components)
3. resources/js/bootstrap.js — exactly as in scratch.md Section 27
   - forceTLS must read from VITE_REVERB_SCHEME env var (true when https)
   - wsPort and wssPort must both read from VITE_REVERB_PORT
4. resources/views/layouts/app.blade.php — exactly as in scratch.md Section 30
5. railway.app.json, railway.reverb.json, railway.queue.json — exactly as in scratch.md Section 32

ENVIRONMENT:
- .env: local config (sqlite, localhost reverb, http scheme)
- .env.example: production template (pgsql, railway domains, https scheme)
- APP_URL, REVERB_HOST, REVERB_SCHEME, VITE_REVERB_SCHEME must all be consistent
- NEVER mix HTTP and WSS — scheme must match across all env vars

MIDDLEWARE:
- IdentifyPlayer middleware (scratch.md Section 15) — appended to web group
- NgrokHeaders middleware (scratch.md Section 15) — local environment ONLY, never production
- bootstrap/app.php: CSRF except /broadcasting/auth, trustProxies at *, no NgrokHeaders in production

PACKAGE.JSON:
- type: module (ESM)

ROLE IMAGE DIRECTORY (scaffold now, images added later):
Create the following structure:
  public/images/roles/   ← empty directory, ready for {role_key}.png files
  public/images/roles/placeholder.svg ← generic atmospheric silhouette SVG

placeholder.svg requirements:
- Dark theme compatible (accent-warm #C8922A stroke on transparent/dark bg)
- Humanoid silhouette shape
- Viewbox 400x560 (portrait 5:7 ratio)
- No fill on figure — stroke only, subtle
- Used as fallback for ALL 27 roles until real images are added

Blade helper (add to a Blade component or service):
  $roleImagePath = public_path("images/roles/{$roleKey}.png");
  $roleImageSrc = file_exists($roleImagePath)
      ? asset("images/roles/{$roleKey}.png")
      : asset('images/roles/placeholder.svg');

See scratch.md Section 22 for full image system spec.

VERIFY:
- npm run build succeeds
- php artisan serve starts
- No mixed content warnings possible from this config
- public/images/roles/placeholder.svg exists and renders correctly
```

---

## PROMPT 02 — Database Migrations & Models

```
Read scratch.md sections 4 and 5 carefully before starting.

Create all 7 migrations in order:

1. create_rooms_table
   - id, code VARCHAR(6) UNIQUE, host_player_id FK nullable,
     status VARCHAR DEFAULT 'waiting', narration_mode VARCHAR DEFAULT 'human',
     night_mode VARCHAR DEFAULT 'narrator_driven', settings JSON, timestamps

2. create_players_table
   - id, room_id FK, nickname VARCHAR, session_token VARCHAR UNIQUE,
     role_id FK nullable, is_alive BOOL DEFAULT true, is_host BOOL DEFAULT false,
     is_narrator BOOL DEFAULT false, voting_banned BOOL DEFAULT false,
     is_silenced BOOL DEFAULT false, is_slave BOOL DEFAULT false,
     master_id FK->players nullable, seat_position INT nullable, timestamps

3. create_roles_table
   - id, key VARCHAR UNIQUE, faction VARCHAR, night_order INT nullable,
     abilities JSON nullable, win_condition VARCHAR, timestamps

4. create_game_states_table
   - id, room_id FK UNIQUE, phase VARCHAR DEFAULT 'waiting',
     round INT DEFAULT 1, data JSON nullable, timestamps

5. create_night_actions_table
   - id, game_state_id FK, player_id FK, action_type VARCHAR,
     target_id FK->players nullable, metadata JSON nullable,
     resolved_at TIMESTAMP nullable, timestamps

6. create_votes_table
   - id, game_state_id FK, voter_id FK->players, target_id FK->players,
     round_type VARCHAR DEFAULT 'initial', timestamps

7. create_couple_bonds_table
   - id, game_state_id FK, player_id FK->players, partner_id FK->players, timestamps

MODELS:
Create all 7 Eloquent models as specified in scratch.md Section 5:
- Room, Player, Role, GameState, NightAction, Vote, CoupleBond
- All fillable arrays, casts, and relationships exactly as specified
- Room: routeKey = code
- Player: casts all boolean fields

GAME_STATES DATA:
- GameState model must cast data as array
- Default data JSON must include ALL keys from scratch.md Section 4 (game_states.data JSON keys)
- Initialize with correct defaults on GameState creation

SEEDER:
- RoleSeeder: seed all 27 roles from scratch.md Section 6
  - All keys, factions, night_orders, abilities, win_conditions
  - Use updateOrCreate on key
- DatabaseSeeder: calls RoleSeeder

VERIFY:
- php artisan migrate runs clean
- php artisan db:seed runs clean
- All 27 roles seeded correctly
```

---

## PROMPT 03 — Authentication & AppServiceProvider

```
Read scratch.md sections 15, 16, and 26 carefully before starting.

AUTHENTICATION:
- No user accounts. Identity = session_token (UUID stored in players.session_token)
- Cookie name: session_token, httpOnly

AppServiceProvider boot():
- Date::use(CarbonImmutable::class)
- DB::prohibitDestructiveCommands in production
- Locale from session: read 'locale' key, set if in ['en', 'fr']
- Register 'session-token' auth guard via auth()->viaRequest():
  → reads cookie 'session_token'
  → returns Player::where('session_token', $token)->first() or null

config/auth.php:
- Add 'session-token' guard with driver 'session-token'

IdentifyPlayer middleware:
- Reads session_token cookie
- Finds Player
- Merges _player onto request
- Does NOT abort if missing

WebSocket channel auth (routes/channels.php):
- player.{playerId}: user exists && user->id === (int)$playerId
- narrator.{roomId}: user exists && user->room_id === (int)$roomId && user->is_narrator
- werewolves.{roomId}: user exists && user->room_id === (int)$roomId && user->role && user->role->faction === 'werewolves'
- room.{roomId}: user exists && user->room_id === (int)$roomId

VERIFY:
- Auth guard resolves Player from cookie correctly
- Channel auth rejects unauthorized access
- Broadcasting auth endpoint works (/broadcasting/auth)
```

---

## PROMPT 04 — Lobby: CreateRoom, JoinRoom, LobbyService

```
Read scratch.md sections 9 (LobbyService), 10, 11, 16, 17 carefully before starting.

LOBBYSERVICE:
createRoom(nickname, locale):
- Generate unique 6-char uppercase room code
- Create Room record (status: waiting)
- Create host Player as narrator (is_narrator=true, is_host=true)
- Store session_token in httpOnly cookie
- Store locale in session
- Return Room

joinRoom(Room, nickname, Request):
- Validate: room status must be 'waiting'
- Validate: nickname not duplicate in room
- Validate: max 24 players
- Create Player (is_narrator=false)
- Store session_token in cookie
- Fire PlayerJoined event on room.{id}
- Return Player

validateGameStart(Room):
- Min 4 players
- Role count must equal player count (excluding narrator)
- At least 1 werewolf faction role
- Two Sisters: exactly 0 or 2
- Three Brothers: exactly 0 or 3
- Solo roles: max 1 each (Cupid, Kira, Angel, Pied Piper, etc.)
- Return errors[] — empty means valid

LIVEWIRE COMPONENTS:
CreateRoom:
- $nickname property
- submit() calls LobbyService::createRoom, JS redirect to /room/{code}/narrator

JoinRoom:
- $code, $nickname properties
- submit() calls LobbyService::joinRoom, JS redirect to /room/{code}/player

CONTROLLERS (thin):
LobbyController:
- create: validate → LobbyService::createRoom → redirect
- join: validate → LobbyService::joinRoom → redirect

ROUTES (web.php):
- GET /create → CreateRoom Livewire
- GET /join/{code?} → JoinRoom Livewire
- POST /api/rooms → LobbyController@create
- POST /api/rooms/join → LobbyController@join

HELPERS:
- QrHelper::generate(string $data): string — as in scratch.md Section 25

VIEWS:
- welcome.blade.php: language toggle (EN/FR), links to create/join
- All strings via __('lobby.*') and __('ui.*')

VERIFY:
- Room created with unique code
- Host player marked is_narrator=true
- Joining player gets session_token cookie
- PlayerJoined event fires and broadcasts to room.{id}
- Validation errors surface correctly
```

---

## PROMPT 05 — Narrator Lobby (NarratorLobby Livewire)

```
Read scratch.md sections 12 and 22 (Lobby Phase) carefully before starting.

NarratorLobby Livewire component:

PROPERTIES:
- $room, $players (polled), $roleComposition[], $nightOrder[],
  $seatOrder[], $difficultySettings[], $disclosureSettings[],
  $presets[], $validationErrors[]

QR + JOIN PANEL:
- Display QR code (QrHelper::generate(APP_URL . '/join/' . $room->code))
- Room code in Cinzel font below QR
- Live player list updating via polling every 3s
- Each player row: nickname, joined time, drag handle for seat order, [Kick] button
- Removing a player from lobby: delete player record

SEAT ORDER:
- Drag-and-drop player list into circular seat positions
- Visual circular seating preview
- Saved to $seatOrder array (player_ids in order)
- Locked permanently when game starts

ROLE COMPOSITION:
- All 27 roles grouped by faction: Village / Werewolf / Neutral
- Each role card: name, description tooltip, +/- counter (min 0)
- Hard validations enforced live:
  → two_sisters: exactly 0 or 2
  → three_brothers: exactly 0 or 3
  → solo roles: max 1 each
  → total roles must equal alive (non-narrator) player count
- Validation errors shown inline per role
- [Start Game] button disabled until all validations pass

NIGHT ORDER:
- Drag-and-drop reorderable list of active roles only
- Default order from scratch.md Section 7
- [Reset to Default] button

DIFFICULTY SETTINGS:
- Night Mode: toggle [Narrator-Driven] / [Simultaneous]
- Silencer vote ban: toggle (default: off)
- Bear Tamer: [Public growl] / [Narrator only]
- Kira: [Unknown death] / [Hidden completely]

INFORMATION DISCLOSURE:
- Per faction toggle: show/hide roles list to players before game
- Or per individual role toggle

PRESETS:
- [Save as Preset] → prompt for name → saved to room settings JSON
- [Load Preset] → dropdown → loads into composition (always editable)
- Auto-suggest recommended composition when player count detected

START GAME:
- Calls RoleAssignmentService::assign(Room)
- Redirects narrator to /game/{room}/narrator

VERIFY:
- QR generates correctly and points to join URL
- Role +/- counters work with live validation
- Drag-and-drop seat order saves correctly
- Night order drag-and-drop saves correctly
- [Start Game] blocked until all validations pass
```

---

## PROMPT 06 — Role Assignment & Game Start

```
Read scratch.md sections 9 (RoleAssignmentService), 13 (Events: GameStarted, RoleAssigned) carefully.

RoleAssignmentService::assign(Room):

DB TRANSACTION:
1. Build role pool from room->settings['role_composition']
2. Shuffle pool
3. Assign one role per non-narrator player
4. Set players.seat_position from lobby seat_order
5. Create GameState with:
   - phase: 'night', round: 1
   - data: full default JSON from scratch.md Section 4
   - seat_order populated in data
   - silencer_ability_count: 1 if players <= 10, else 2

IMMEDIATE NOTIFICATIONS (on player.{id} channels):
- Every player: fire RoleAssigned with role_key, faction, night_order, abilities
- Two Sisters: each receives partner nickname
- Three Brothers: each receives both brothers' nicknames
- Werewolf pack (all wolves): each receives list of packmates
- Wolf Hound: no pack notification yet (not until night 1 choice)
- Cupid, Seer, Witch, Bodyguard, etc.: role card data only
- Kira: night 1 notification goes to NARRATOR only (via narrator.{room_id}):
  "Kira is [Nickname]" in Action Feed

AFTER ASSIGNMENT:
- Fire GameStarted on room.{id}
- Fire RoleAssigned on player.{id} for each player
- Update room status to 'playing'

VERIFY:
- All 27 role types assigned correctly
- Sisters/Brothers/Wolves notified at correct moment
- Kira identity only reaches narrator channel
- GameState data JSON contains all required keys with correct defaults
- Room status changes to 'playing'
- PlayerGameView and PlayerLobby redirect on GameStarted event
```

---

## PROMPT 07 — Game Engine & Phase Manager

```
Read scratch.md sections 9 (GameEngine, PhaseManager, WinConditionChecker) carefully.

GAMEENGINE:
- startGame(Room) → delegates to RoleAssignmentService
- advancePhase(GameState, string $to) → delegates to PhaseManager
- resolveVote(GameState) → delegates to VotingService, then WinConditionChecker
- resolveNight(GameState) → delegates to ActionResolver, then WinConditionChecker
- eliminatePlayer(Player, GameState) → set is_alive=false, fire PlayerEliminated, run WinConditionChecker
- endGame(GameState, FactionInterface) → store winning_faction in data, set phase to 'finished', fire GameFinished

PHASEMANAGER (ONLY class that writes $state->phase):
Transitions allowed:
- waiting → night
- night → day OR finished
- day → defense (narrator opens defense window)
- defense → voting (defense closed, final vote opens)
- voting → night OR day OR finished

On every transition:
- Write $state->phase
- Save GameState
- If going to night: increment round, delete votes for previous round
- Fire PhaseChanged on room.{id} with phase and round

WINCONDITIONCHECKER::check(GameState):
Priority order (check in this order, return on first match):
1. Angel: round===1 && data.angel_eliminated_by_vote===true
2. White Werewolf: exactly 1 alive player with role white_werewolf
3. Pied Piper: count(alive players) === count(data.enchanted_player_ids intersect alive)
4. Kira: data.kira_correct_count === 3
5. Werewolves: alive_wolf_count >= alive_village_aligned_count
   (wolf_hound counts as wolf if wolf_hound_choice === 'werewolf')
   (silencer counts as wolf)
6. Village: no alive wolves (werewolf, big_bad_wolf, accursed_wolf_father, wolf_hound if chose werewolf, silencer)
7. Lovers: exactly 2 alive players who form a CoupleBond pair with different factions

Returns winning faction or null.
WinConditionChecker runs after: every PlayerEliminated, every vote resolution, every enchant.

RULE: NEVER write $state->phase outside of PhaseManager.
RULE: Death chains (Hunter shot, lover death, Knight infection) must fully resolve before WinConditionChecker.

VERIFY:
- Phase transitions fire correct events
- Win condition priority order is exact
- WinConditionChecker runs at correct moments
- endGame stores winning faction and fires GameFinished
```

---

## PROMPT 08 — Action System (Interface, BaseAction, ActionResolver)

```
Read scratch.md sections 8 (Action Types, Resolution Flow, ActionInterface) carefully.

ACTIONINTERFACE:
- getActingRole(): string
- getTarget(): ?Player
- isValid(GameState $state): bool
- resolve(GameState $state): void
- getPriority(): int

BASEACTION:
- Receives NightAction model via constructor
- Resolves target Player from target_id
- Base isValid checks: phase==='night', player is_alive, target is_alive (if required)

ACTIONSERVICE::submit(Player, data):
- Validate: phase is night
- Validate: player is alive
- Validate: player's role matches action type
- Validate: no duplicate submission for this player this night
- Create NightAction (resolved_at=null)
- Append to data.action_history
- Fire NightActionSubmitted on narrator.{room_id}
- Return NightAction

ACTIONRESOLVER::resolve(GameState):
Resolution order (MUST follow exactly):

1. Knight Rusty Sword delayed death:
   - If data.infected_werewolf_id is set → eliminate that player first
   - Clear infected_werewolf_id
   - Run death chain checks

2. Load all unresolved NightActions for this game_state
3. Sort by priority (getPriority())
4. Process in order, collecting:
   - kill targets (werewolf faction)
   - protect targets (bodyguard)
   - save target (witch)
   - poison target (witch)
   - convert target (wolf father)
   - solo kill target (white werewolf)
   - enchant targets (pied piper)
   - inspect results (seer, fox)
   - silence targets (silencer)
   - slave recruits (master)
   - Kira guess (kira)

5. Wolf Father convert: replaces wolf kill entirely, skip wolf kill target
6. Apply deaths:
   - Cancel if bodyguard protected (blocks: wolf kill, witch poison)
   - Cancel if wolf father converted (target becomes wolf instead)
   - Cancel if witch saved (same target as wolf kill)
   - Add witch poison target to deaths
   - Add white werewolf solo kill to deaths
   - Add Kira kill (correct guess) to deaths (silently)

7. Death chain for each death:
   a. Eliminate player (set is_alive=false, fire PlayerEliminated)
   b. Check CoupleBond — if partner alive, eliminate partner (LoverDied event)
   c. Check Hunter — if Hunter eliminated, activate Hunter shot panel
   d. Check Knight Rusty Sword — set infected_werewolf_id if wolf killed Knight
   e. Run WinConditionChecker after every death
   f. Stop if game won

8. Kira wrong guess: data.kira_remaining_guesses -= 1
   If 0: eliminate Kira silently, announce "unknown cause" or hide (difficulty setting)

9. Mark all NightActions as resolved (resolved_at = now())
10. Clear silenced players (is_silenced=false, data.silenced_player_ids=[])
11. Fire NightResolved on room.{id} with eliminated nicknames[]

VERIFY:
- Knight curse resolves before all other actions
- Bodyguard blocks wolf kill AND witch poison only (not BBW extra, not White WW, not Hunter, not Kira)
- Wolf Father convert cancels wolf kill entirely
- Death chains resolve completely before WinConditionChecker final check
- Silence clears after resolution
- All NightActions marked resolved_at
```

---

## PROMPT 09 — Village Role Actions

```
Read scratch.md Section 21 carefully for all village role specs before starting.

Implement the following action classes (all extend BaseAction, implement ActionInterface):

CUPIDLINKACTION (priority 1):
- Night 1 only (round === 1, once/game)
- Creates 2 CoupleBond records (player_id↔partner_id both ways)
- Stores lover_info in data
- Immediately fires private notification to each lover via player.{id}:
  "You are in love with [partner] — faction: [partner's faction]"
- Narrator sees both identities in Relations panel and Action Feed
- Neither lover learns who linked them

BODYGUARDPROTECTACTION (priority 4):
- Cannot protect same player twice in game (check data.bodyguard_protected_ids)
- Adds target to data.bodyguard_protected_ids
- Sets data.bodyguard_last_protected_id
- If all alive players already in protected_ids → action not available (decoy shown)
- Silent — no notification to protected player

WITCHSAVEACTION (priority 9):
- Once/game (data.witch_save_used must be false)
- Cancels wolf kill on same target
- Sets data.witch_save_used = true
- Witch sees wolf kill target before deciding

WITCHPOISONACTION (priority 10):
- Once/game (data.witch_poison_used must be false)
- Independent kill (not cancelled by bodyguard)
- Wait — bodyguard DOES block witch poison (scratch.md Section 8)
- Sets data.witch_poison_used = true
- Both potions spent → Witch sees villager decoy panel (not her own greyed panel)

PIERPIPERENENCHANTACTION (priority 11):
- Adds target to data.enchanted_player_ids
- Target notified on player.{id}: enchanted message
- WinConditionChecker runs immediately after every enchant

FOXINSPECTACTION (priority 12):
- 3 adjacent targets by seat_order (left, center, right)
- Check if any of the 3 is werewolf faction (includes wolf_hound if chose wolf, white_werewolf)
- Correct (wolf found): fire FoxResultReady on player.{id} with werewolf_found=true
- Wrong (no wolf): fire FoxResultReady with werewolf_found=false, set data.fox_ability_active=false
- No public announcement on wrong guess

SEERINSPECTACTION (priority 13):
- Cannot inspect same player twice (track in data.seer_results keys)
- Binary result: target is wolf faction or not
- Fire SeerResultReady on player.{id}: target_nickname + is_werewolf (boolean)
- Result stored in data.seer_results[target_id] = is_werewolf
- No public announcement

MASTERENSLAVEACTION (priority 2):
- Night 1: picks 2 slaves
- Night 2+: picks 1 additional slave
- Cannot enslave already enslaved players
- Cannot enslave self
- Can enslave any faction (village, werewolf, neutral)
- New slave notified: master identity + fellow slaves
- Existing slaves notified: new slave joined
- Sets player.is_slave=true, player.master_id=master.id
- Appends to data.master_slave_ids

VERIFY:
- Cupid link fires immediately on submit (not at dawn)
- Bodyguard protection list persists across rounds
- Witch panels switch to decoy correctly when both potions used
- Seer history accumulates correctly
- Fox loses ability permanently on wrong guess
- Master slavery survives across rounds
- Master death frees all slaves
```

---

## PROMPT 10 — Werewolf Role Actions

```
Read scratch.md Section 21 carefully for all werewolf role specs before starting.

WEREWOLFKILLACTION (priority 5):
- Collective consensus required — all alive wolves must select same target
- Shared kill panel: live tally visible to all wolves
- [Confirm Kill] only activates when all living wolves chose same target
- Any wolf can change vote until confirmed
- Target must be non-wolf alive player
- Store wolf_kill_target in metadata

BIGBADWOLFKILLACTION (priority 6):
- Only available if no wolf has died yet
- If any wolf died: BBW sees regular wolf panel only, no extra kill
- Independent kill, cannot pick same target as wolf kill
- On first wolf death: notify BBW on player.{id} that ability is lost
- Narrator Event Feed: "Big Bad Wolf ability lost"

ACCURSEDWOLFFATHERCONVERTACTION (priority 7):
- Once/game (data.wolf_father_used must be false)
- Replaces wolf kill ENTIRELY that night (wolf kill cancelled)
- Target must be non-wolf alive player
- On convert:
  → data.wolf_father_used = true
  → Target role changed to 'werewolf' (or closest equivalent)
  → Target notified on player.{id}: converted + pack list
  → Pack notified on player.{id}: new wolf joined
  → Narrator Relations updated
- After use: Wolf Father sees regular wolf panel

WHITEWEREWOLFKILLACTION (priority 8):
- Even rounds only (round % 2 === 0)
- Targets wolves only (excluding self)
- Silent — pack not notified
- No public announcement until dawn
- Participates in regular wolf kill too (every night)

WOLFHOUNDCHOICEACTION (priority 3 — night 1 only):
- Night 1 only, irreversible
- [Join the Village] or [Join the Pack]
- Hold-to-confirm with irreversible warning
- On Village: wolf_hound_choice='village', treated as villager forever
- On Werewolf:
  → wolf_hound_choice='werewolf'
  → Added to wolf pack channel (werewolves.{roomId})
  → Pack notified of new wolf
  → Wolf Hound notified of pack members
  → Wolf Hound now sees wolf kill panel from night 2

SILENCERSILENCEACTION (priority 3 — in night order):
- Count from data.silencer_ability_count (1 if ≤10 players, 2 if >10)
- Can silence any alive player any faction
- Sets is_silenced=true, adds to data.silenced_player_ids
- At dawn: fire PlayerSilenced on room.{id} with nicknames[]
- Narrator announces publicly who is silenced
- Silenced player notified on player.{id}: silence badge appears
- Silencer also participates in regular wolf kill

VERIFY:
- Wolf consensus panel updates live for all wolves simultaneously
- BBW ability check runs correctly on every wolf death
- Wolf Father conversion fires correct notifications to target + pack
- White Werewolf cadence: night 1=no, night 2=yes, night 3=no, etc.
- Wolf Hound not added to wolf channel until explicit night 1 choice
- Silence clears at next night transition
- Silencer_ability_count set correctly at game start based on player count
```

---

## PROMPT 11 — Neutral Role Actions

```
Read scratch.md Section 21 carefully for neutral role specs before starting.

ANGEL (no action class needed — passive win condition):
- No night action → decoy panel always
- WinConditionChecker handles: round===1 && angel_eliminated_by_vote===true
- If Angel survives round 1 vote:
  → Private on player.{id}: "Your divine window has passed. You now fight for the village."
  → Narrator Action Feed: "Angel joins village — divine favor expired"
- If Angel killed by wolves round 1: normal death, no win

PIERPIPERENENCHANTACTION (priority 11 — already in Prompt 09):
- Confirm: win check runs after EVERY enchant not just eliminations
- Confirm: target cannot be already enchanted, cannot be Pied Piper himself
- Confirm: enchanted badge persists on target's screen (maskable)
- Confirm: enchanted players still play normally (no ability change)

KIRA GUESSACTION (priority 14 — always last):
- Night 1: Kira sees villager decoy. Narrator sees "Kira is [Nickname]" in Action Feed.
- Night 2+: Panel activates with [Yes — Make a Guess] or [Skip Tonight]

On [Yes]:
- Step 1: pick alive target from player list
- Step 2: pick role from full role list (villager EXCLUDED)
- Hold-to-confirm

On CORRECT guess:
- Target eliminated silently
- Death announced as "unknown cause" (or fully hidden per difficulty setting)
- Kira on player.{id}: "Correct — [Nickname] was [Role]. Guesses restored to 3."
- data.kira_correct_count += 1
- data.kira_remaining_guesses = 3
- data.kira_correct_targets.push(target_id)
- Narrator Action Feed: "Kira correctly guessed [Nickname] as [Role]"
- WinConditionChecker runs (check kira_correct_count === 3)

On WRONG guess:
- Target survives — no public announcement
- Kira on player.{id}: "Wrong — [X] guesses remaining"
- data.kira_remaining_guesses -= 1
- Narrator Action Feed: "Kira wrong guess — [X] guesses remaining"
- If data.kira_remaining_guesses === 0:
  → Eliminate Kira silently
  → Announce "unknown cause of death" (or hide per difficulty)
  → Narrator Action Feed: "Kira eliminated — guesses exhausted"

KIRA UI:
- Narrator player card shows current guess count badge at all times
- Kira's own screen shows current guess count below action panel
- Difficulty setting controls public visibility of Kira kills

VERIFY:
- Kira cannot guess 'villager' role
- Kira kills are independent of bodyguard (bodyguard does NOT block Kira)
- Kira enslaved by Master can still kill Master
- Angel win only triggers on vote elimination in round 1
- Pied Piper win check runs after every single enchant
```

---

## PROMPT 12 — Voting System & Defense Window

```
Read scratch.md sections 9 (VotingService), 22 (Vote Controls), 24 (Edge Cases) carefully.

VOTINGSERVICE:

submitVote(voter, target, state, round_type):
- Validate: phase is 'voting' OR 'defense' closed (final vote open)
- Validate: voter is alive
- Validate: voter is not voting_banned
- Validate: voter is not silenced with vote ban (if difficulty setting enabled)
- Validate: if voter is_slave → master must have voted first
- If voter is_slave: force target to match master's vote
- Create Vote record with round_type ('initial' or 'final')
- Fire VoteSubmitted on narrator.{room_id}

tally(state, round_type):
- Count votes per target for given round_type
- Return counts[] sorted by votes descending

resolve(state):
DB TRANSACTION:
- Use 'final' round_type votes (after defense window)
- Find winner(s) by tally
- Tie handling:
  → Check if Scapegoat alive → eliminate Scapegoat (fires scapegoat decree first)
  → If no Scapegoat → second vote among tied players only
  → Still tied → no elimination (NEVER random)
- Village Idiot check: if winner is village_idiot → VillageIdiotRevealed event, voting_banned=true, player survives
- Elder check: if winner is elder and first time voted → check elder_abilities_disabled
- Devoted Servant check: if pre-submitted [Yes] → swap roles silently before announcement
- Eliminate winner: set is_alive=false, fire PlayerEliminated
- Death chain: Hunter shot, lover death, etc.
- WinConditionChecker runs
- Fire PhaseChanged

DEFENSE WINDOW:
openDefense(state):
- Set data.defense_window_open = true
- Fire DefenseWindowOpened on room.{id} with defense_player_ids[]
- Players see: "Defense in progress — narrator is speaking"
- No app interaction during defense (verbal/physical only)
- Narrator chooses who speaks verbally

closeDefense(state):
- Set data.defense_window_open = false
- Fire DefenseWindowClosed on room.{id}
- Clear initial votes
- Set data.vote_phase = 'final'
- Fire FinalVoteOpened on room.{id}
- Final vote opens automatically

DEFENSECONTROLLER (thin):
- POST /api/defense/open → narrator verified → VotingService::openDefense
- POST /api/defense/close → narrator verified → VotingService::closeDefense

NARRATOR VOTE CONTROL SEQUENCE:
1. [Close Initial Vote] → narrator triggered
2. [Open Defense] → narrator triggered → DefenseWindowOpened fires
3. [Close Defense] → narrator triggered → DefenseWindowClosed + FinalVoteOpened fires
4. Final vote runs
5. [Close Final Vote] → narrator triggered
6. [Announce Elimination] → narrator triggered → fires PlayerEliminated

SLAVE VOTE MECHANICS:
- Slave voting panel locked: "Waiting for your master to vote..."
- Master votes → slaves on player.{id}: master's choice pre-selected
- Slave panel unlocks → slave can only confirm, not change target

SCAPEGOAT DECREE:
- On tie → before elimination → Scapegoat panel activates:
  "You are the scapegoat. Choose who may vote next round."
- Two lists: [Can Vote] / [Cannot Vote] for all alive players
- Hold-to-confirm → submitted → then elimination announced
- data.vote_ban_next_round populated
- Banned players notified: "You cannot vote this round"

STUTTERING JUDGE SECOND VOTE:
- Hidden [Request Second Vote] button in judge's vote panel
- On submit: data.stuttering_judge_used=true, silent signal to narrator
- Narrator dashboard: [Trigger Second Vote] button appears
- Narrator taps → second vote triggers manually

VERIFY:
- Defense window flow: initial → defense → final → resolution
- Slave vote lock enforced server-side
- Scapegoat decree fires before elimination
- Devoted Servant swap is invisible (no UI tell)
- Village Idiot survives vote, banned from future voting
- Elder vote-out disables all village abilities
- Stuttering Judge button hidden from everyone except Judge
```

---

## PROMPT 13 — Narrator Dashboard (NarratorDashboard Livewire)

```
Read scratch.md Section 22 (Game Dashboard) fully before starting.

NarratorDashboard Livewire component — full game control panel.

LAYOUT (4 persistent areas):
- Phase bar (top)
- Player grid (left)
- Live areas 3 tabs (right)
- Night/vote controls (bottom)

PHASE BAR:
- Current phase label (NIGHT / DAY / DEFENSE / VOTING)
- Current round number
- [Advance Phase] button — narrator triggered, calls PhaseManager
- Smart prompts (appear when conditions met):
  → "All night actions submitted — ready to resolve"
  → "All votes in — ready to close voting"
  → "Stuttering Judge requested second vote"
  → "Knight's curse — [Nickname] must die first"
  → "Devoted Servant must decide — waiting"
  → "Bear Tamer check: Growl 🐻 or Silent 🤫"
  → "Kira eliminated — announce unknown cause"
  → "Elder voted out — village abilities disabled"
- All prompts dismissible

PLAYER GRID:
Each player card:
- Nickname
- Role name + faction color border
- Alive/dead/disconnected status dot
- Mode 2 submission badge (✓ / ⏳)
- Special badges: Silenced 🤫 / Slave 👑 / Enchanted 🎵 / Infected ☠️ / No Vote 🚫 / Idiot 🤡 / Kira [X guesses]
- [Kick] button → removes player, slot reopens for new joiner
- [Send Message] button → fires NarratorMessageSent on player.{id}

Contextual narrator buttons per card:
- [Little Girl Caught] — visible during wolf phase only → immediate elimination
- [Trigger Second Vote] — visible after Stuttering Judge signals
- [Force Resolve] — Mode 2 night only

LIVE AREAS — 3 TABS:
Tab 1 — Event Log:
- Timestamped feed, newest at top
- Color coded by event type
- All game events: phases, deaths, eliminations, wins, disconnections, silences

Tab 2 — Action Feed (narrator only, never player-visible):
- "[Nickname/Role] → [Action] → [Target]"
- Decoy submissions tagged [DECOY]
- Pending: muted color | Resolved: full color

Tab 3 — Relations:
- Lovers 💕 → A + B (factions shown)
- Wolf Pack 🐺 → A, B, C (updates on Wolf Hound choice + Wolf Father convert)
- Enchanted 🎵 → A, B, C... (grows each night)
- Slaves 👑 → Master: X → Slaves: A, B, C (grows each night)
- Infected ☠️ → [Nickname] — dies next night (clears after)
- Sisters 👯 → A + B
- Brothers 👨‍👨‍👦 → A + B + C
- Seat Order (always visible below relations):
  → Circular: A — B — C — D ... → A
  → Bear Tamer highlighted, neighbors marked at dawn
  → Fox adjacency shown when Fox acts
  → Dead players greyed but stay in position

NIGHT CONTROLS:
Mode 1 (Narrator Driven):
- Wake order queue from configured night order
- Current role card highlighted: "Wake [Role name]"
- [Mark Done] advances to next
- Auto-skips per rules in scratch.md Section 7 (auto_skip_rules)
- Bear Tamer shows growl result in queue card

Mode 2 (Simultaneous):
- Per-player submission grid: all players, submitted ✓ / pending ⏳
- Narrator sees exactly who has not submitted
- [Force Resolve] button — no auto-timeout
- After gate opens: same death summary flow as Mode 1

Both modes post-resolution:
- "Tonight's deaths: [A], [B]" shown privately to narrator
- [Announce Deaths] → fires NightResolved on room.{id}

VOTE CONTROLS:
Full flow per scratch.md Section 22 (Vote Controls):
- Live tally per candidate (narrator only)
- Full voter breakdown: "[Voter] → [Target]"
- Slave voters marked 👑 (locked until master)
- Silenced players marked 🤫
- Banned voters marked 🚫
- Control buttons in sequence:
  [Close Initial Vote] → [Open Defense] → [Close Defense]
  → [Final Vote auto-opens] → [Close Final Vote]
  → [Announce Elimination]

EVENTS LISTENED TO:
- All 21 events (update relevant sections of dashboard in real-time)

GAME OVER:
- Winning faction prominent display
- Full role reveal: every player + their role
- Game summary: rounds, deaths, key events
- [Start New Game] → clears state, returns to lobby with same players
- [End Session] → dissolves room

VERIFY:
- All 21 events update dashboard in real-time
- Smart prompts appear at correct moments
- Player grid badges update live
- Night Mode 1 queue skips correctly
- Night Mode 2 force resolve works
- Defense window sequence buttons appear/disappear in correct order
- Relations panel updates on Wolf Hound choice, Wolf Father convert, Pied Piper enchant
```

---

## PROMPT 14 — Player Game View (PlayerGameView Livewire)

```
Read scratch.md sections 12 (PlayerGameView, NightAction, RoleCard, VotingPanel) and 23 (Player Interface) carefully.

PlayerGameView Livewire component — player's personal screen.

DEFAULT STATE (day/waiting):
- Minimal atmospheric screen
- Room code, round number, phase label
- Alive/dead status
- Role card access (hold-to-reveal)
- If silenced: "🤫 You have been silenced today — you may not speak"
- If enchanted: "🎵 You are enchanted" (maskable badge)
- If slave: "👑 Your master controls your vote"

ROLECARD subcomponent:
- Masked state: atmospheric card face, "?" centered — NO image shown
- Hold-to-reveal (hold 1.5s) → card flips to revealed state
- Revealed state layout (see scratch.md Section 22):
  → Role image as full card background (object-fit: cover)
  → If public/images/roles/{role_key}.png exists → use it
  → If not → use public/images/roles/placeholder.svg (generic silhouette)
  → Dark gradient overlay: linear-gradient(to top, rgba(0,0,0,0.92) 40%, rgba(0,0,0,0.3) 100%)
  → Text overlaid at bottom: role name (Cinzel), faction, night_order, abilities
  → Card: position relative, overflow hidden
  → Image: position absolute, inset 0, z-index 0
  → Gradient: position absolute, inset 0, z-index 1
  → Text: position relative, z-index 2
- Always accessible — player can re-check anytime
- Stays maskable after reveal (hold again to mask)

NIGHT PHASE (all players see decoy immediately):
- Decoy panel appears for EVERYONE at night phase start
- Visually identical to a real action panel
- Puzzle type from lang/decoys.php (math, riddle, count, unscramble, sequence)
- [Next Puzzle] refreshes client-side from lang arrays (no server call)
- Mode 2: submitting decoy checks player into the all-ready gate
- Mode 1: no screen change when narrator calls a role (narrator uses voice)

NIGHTACTION subcomponent (shown instead of decoy for acting roles):
Role-specific panels — implement all unique panels:

Cupid: 2-step picker (Step 1: first lover, Step 2: second lover with first locked)
Seer: single target picker, inspected players greyed, maskable result history
Witch: wolf kill target shown, [Save Potion] button, [Poison Potion] button with target list, both greyed after use
Hunter: activates on elimination — target picker before death announced
Bodyguard: target list with already-protected greyed out
Wolf pack: shared consensus panel with live tally, [Confirm Kill] when all agree
Wolf Hound night 1: [Join the Village] / [Join the Pack] with hold-to-confirm
Accursed Wolf Father: wolf panel + [Use Convert Instead] toggle
White Werewolf: wolf panel + solo kill panel on even rounds (wolves only list)
Big Bad Wolf: wolf panel + extra kill panel (if no wolf dead)
Pied Piper: target list with enchanted + self greyed out
Fox: tap one player → auto-selects 3 adjacent by seat_order
Master night 1: 2-step picker | Night 2+: single slave picker
Kira: [Skip Tonight] or [Yes] → step 1 target → step 2 role (no villager)
Silencer: 1 or 2 target pickers based on silencer_ability_count
Stuttering Judge: normal vote panel + hidden [Request Second Vote] button
Devoted Servant: normal vote panel + pre-decision prompt at vote phase start

Hold-to-reveal on submitted state: "Done" state maskable

VOTINGPANEL subcomponent:
- Target list of alive players
- Slave: locked panel "Waiting for your master to vote..." → unlocks with master's choice pre-selected
- Banned: "You cannot vote this round"
- Silenced (with vote ban difficulty): cannot vote message
- Stuttering Judge: hidden second vote button
- Devoted Servant: pre-decision prompt
- Live tally: narrator only (players never see tally)
- Confirmation step before submitting

EVENTS LISTENED TO (player.{id} + room.{id}):
- RoleAssigned → show role card
- GameStarted → redirect to player game view
- PhaseChanged → update phase overlay
- NightResolved → show deaths
- PlayerEliminated → update status
- PlayerSilenced → show silence badge
- SeerResultReady → show result (player.{id})
- FoxResultReady → show result (player.{id})
- NarratorMessageSent → show message overlay (player.{id})
- LoversRevealed → show lover notification (immediate on Cupid submit)
- VillageIdiotRevealed → show public announcement
- DefenseWindowOpened → show "Defense in progress" overlay
- FinalVoteOpened → reopen voting panel
- GameFinished → show faction win screen

VERIFY:
- Decoy panel appears for ALL players at night (not just non-acting)
- Mode 1: no screen change when narrator calls a role
- Mode 2: decoy submission counts as gate check-in
- Seer history accumulates and is maskable
- Witch panel switches to decoy when both potions spent
- Slave vote lock works correctly
- Lover notification fires immediately on Cupid submit
- Defense window overlay appears/disappears correctly
- All private results (Seer, Fox, Kira, Lovers) only on player.{id} channel
```

---

## PROMPT 15 — Passive Role Triggers & Edge Cases

```
Read scratch.md Section 24 (Edge Cases) fully before starting.

Implement all passive role triggers not covered in previous prompts:

HUNTER LAST SHOT:
- Triggers on ANY elimination (wolf kill, vote, Kira kill, etc.)
- Before death announced publicly
- Hunter's screen activates: target picker
- No time limit — narrator waits
- After Hunter submits: target eliminated → death chain → WinConditionChecker
- Bodyguard does NOT block Hunter shot

KNIGHT RUSTY SWORD INFECTION:
- Only triggers when wolf faction kills the Knight
- infected_werewolf_id stored in data
- Infected wolf notified privately on player.{id}
- Next night START (before any action): infected wolf eliminated first
- Narrator gets prompt at night start: "Knight's curse — [Nickname] must die first"
- Cleared from data after resolution

ELDER RESILIENCE + FRAGILITY:
- First wolf attack: Elder survives (server-side only, no death applied)
- Private notification to Elder: "You survived"
- Narrator Event Feed: "Elder survived first wolf attack"
- No public announcement
- Vote-out: elder_abilities_disabled=true, ALL village role panels switch to decoy next night
- Village abilities check: before showing real panel, check elder_abilities_disabled flag

VILLAGE IDIOT VOTE SURVIVAL:
- On vote resolution, before PlayerEliminated event:
- Check if winner is village_idiot
- If yes: NO PlayerEliminated event
- VillageIdiotRevealed fires on room.{id}
- voting_banned=true on player record
- Player survives with "Idiot — No Vote" badge

DEVOTED SERVANT INVISIBLE SWAP:
- Pre-submitted at VOTE PHASE START (before anyone is eliminated)
- If [Yes] pre-submitted and someone eliminated:
  → Swap roles server-side BEFORE public announcement
  → No UI indication a swap happened
  → Public announcement shows only final result (Devoted Servant's new role)
- Narrator Event Feed shows swap privately

SCAPEGOAT LAST DECREE:
- Activated before elimination on tie
- Scapegoat submits vote ban list FIRST
- Then elimination announced
- data.vote_ban_next_round populated
- Banned players notified privately

LITTLE GIRL CAUGHT:
- Narrator dashboard [Little Girl Caught] button (visible during wolf phase only)
- Immediate elimination
- Does NOT count as wolf kill
- Bodyguard does NOT block
- Fires PlayerEliminated normally

ANGEL EXPIRED DIVINE WINDOW:
- If Angel survives round 1 vote → private notification → joins village
- Track in data: angel can never win after round 1

WHITE WEREWOLF SOLO CADENCE:
- Strictly even rounds: 2, 4, 6...
- Night 1 (round 1): no solo kill
- Night 2 (round 2): solo kill available
- Skip White Werewolf solo on odd rounds in all night modes

BEAR TAMER MORNING CHECK:
- Server-side: check Bear Tamer's seat_order neighbors
- Left neighbor = seat_order[(bear_position - 1 + total) % total]
- Right neighbor = seat_order[(bear_position + 1) % total]
- If either is werewolf faction (including wolf_hound if chose wolf): growl=true
- Narrator Dashboard dawn prompt: "Growl 🐻" or "Silent 🤫"
- Difficulty: public (fires PlayerEvent) or narrator only

WOLF HOUND FOX COUNTING:
- Wolf Hound counts as wolf for Fox sniff IF wolf_hound_choice === 'werewolf'
- White Werewolf counts as wolf for Fox sniff always

VERIFY:
- Hunter shot triggers before death announcement, no time limit
- Knight infection resolves before all other night actions next night
- Elder_abilities_disabled flag checked before showing real panels
- Village Idiot survives, no PlayerEliminated fires
- Devoted Servant swap invisible to table
- Bear Tamer uses seat_order circular adjacency (not role proximity)
- White Werewolf strictly even rounds
```

---

## PROMPT 16 — WebSocket Events & Real-time Layer

```
Read scratch.md sections 12 (Events), 13 (Channels), 14 (Channels auth) fully before starting.

Implement all 21 ShouldBroadcast events:

Each event class must:
- Implement ShouldBroadcast
- Define broadcastOn() returning correct private channel
- Define broadcastWith() returning correct data payload
- Never broadcast directly from controllers/services (always fire events)

EVENT LIST with channels and payloads:

AllPlayersReady → PrivateChannel('room.{id}') → {room_id}
DefenseWindowClosed → PrivateChannel('room.{id}') → {room_id}
DefenseWindowOpened → PrivateChannel('room.{id}') → {defense_player_ids[]}
FinalVoteOpened → PrivateChannel('room.{id}') → {room_id}
FoxResultReady → PrivateChannel('player.{id}') → {werewolf_found: bool}
GameFinished → PrivateChannel('room.{id}') → {winning_faction, winner_ids[]}
GameReset → PrivateChannel('room.{id}') → {room_id}
GameStarted → PrivateChannel('room.{id}') → {room_id}
LoverDied → PrivateChannel('room.{id}') → {nickname, partner_nickname}
LoversRevealed → PrivateChannel('player.{id}') → {partner_nickname, partner_faction}
NarratorMessageSent → PrivateChannel('player.{id}') → {message}
NightActionSubmitted → PrivateChannel('narrator.{room_id}') → {action_id, player_id, action_type, target_id}
NightResolved → PrivateChannel('room.{id}') → {eliminated: nicknames[]}
PhaseChanged → PrivateChannel('room.{id}') → {phase, round}
PlayerEliminated → PrivateChannel('room.{id}') → {nickname, role_key, role_name}
PlayerJoined → PrivateChannel('room.{id}') → {player: {id, nickname, is_narrator}, player_count}
PlayerLeft → PrivateChannel('room.{id}') → {player_id, player_count}
PlayerSilenced → PrivateChannel('room.{id}') → {nicknames[]}
RoleAssigned → PrivateChannel('player.{id}') → {role_key, faction, night_order, abilities}
SeerResultReady → PrivateChannel('player.{id}') → {target_nickname, is_werewolf: bool}
SuspiciousAccessAttempt → PrivateChannel('narrator.{room_id}') → {player: {id, nickname}, details}
VillageIdiotRevealed → PrivateChannel('room.{id}') → {nickname}
VoteSubmitted → PrivateChannel('narrator.{room_id}') → {voter_id, target_id, round_type}

CHANNEL AUTH (routes/channels.php):
- player.{playerId}: user && user->id === (int)$playerId
- narrator.{roomId}: user && user->room_id === (int)$roomId && user->is_narrator
- werewolves.{roomId}: user && user->room_id === (int)$roomId && role->faction === 'werewolves'
- room.{roomId}: user && user->room_id === (int)$roomId

LIVEWIRE REAL-TIME:
- NarratorDashboard: listen to all 21 events → update relevant sections
- PlayerGameView: listen to room.{id} + player.{id} → update UI
- NarratorLobby: poll every 3s for player list changes
- PlayerLobby: listen for GameStarted → redirect

VERIFY:
- All 21 events broadcast to correct channels
- Sensitive data (roles, results, lover info, slave info) ONLY on player.{id}
- Narrator-only data (Kira identity, action details) ONLY on narrator.{room_id}
- Channel auth rejects all unauthorized requests
- No event fires directly from controller or service (always via event dispatch)
- CSRF exempt only for /broadcasting/auth
```

---

## PROMPT 17 — Disconnection, Kick & New Game

```
Read scratch.md sections 24 (Edge Cases: Disconnect, New Game) and 22 (Game Over Screen) carefully.

DISCONNECTION HANDLING:
- 2-minute reconnect window before force-kill
- Player reconnects within 2 min: resume from current game state (no penalty)
- Player does NOT reconnect: force-kill (set is_alive=false)
- Death from disconnect: NO death chain effects (no lover death, no Hunter shot)
- WinConditionChecker DOES run after disconnect death
- Narrator Dashboard: player card shows orange "Disconnected" dot

KICK (mid-game):
- Narrator [Kick] button on player card
- Removes player from game (set is_alive=false)
- Slot reopens: room status allows one new player to join
- New joiner gets a fresh player record (no role — narrator assigns manually or game continues)
- Fires PlayerLeft on room.{id}
- WinConditionChecker runs after kick

NEW GAME (after game ends):
- [Start New Game] on narrator game over screen
- DB TRANSACTION:
  → Delete all GameState, NightActions, Votes, CoupleBonds for this room
  → Reset all player records:
    role_id=null, is_alive=true, voting_banned=false,
    is_silenced=false, is_slave=false, master_id=null
  → Keep all Player records (same players)
  → Keep Room record
  → Room status = 'waiting'
- Fire GameReset on room.{id}
- Narrator redirected to /room/{code}/narrator (NarratorLobby)
- Players redirected to /room/{code}/player (PlayerLobby) on GameReset event
- Role composition preserved in room->settings as starting point for next game

END SESSION:
- [End Session] on narrator game over screen
- Dissolves room: delete Room → cascades to Players, GameState, etc.
- All players redirected to welcome page

VERIFY:
- Disconnect death: no death chain, WinConditionChecker still runs
- Reconnect within 2 min: player resumes correctly
- Kick: slot reopens, PlayerLeft fires
- New game: all game records cleared, players reset, room reused
- GameReset event redirects all players to lobby
- End session clears everything
```

---

## PROMPT 18 — Localization (EN/FR)

```
Read scratch.md Section 19 (Localization) carefully before starting.

Create 6 language files for EACH of EN and FR (12 files total):

lang/en/ui.php and lang/fr/ui.php:
- All generic UI strings: buttons, labels, status messages, phase names
- Hold-to-reveal instructions
- Connection status messages
- All narrator dashboard labels
- All player screen labels

lang/en/roles.php and lang/fr/roles.php:
- Name and description for all 27 roles
- Faction label for each role
- Night order label ("Acts on night [X]" or "No night action")
- Abilities description for each role

lang/en/game.php and lang/fr/game.php:
- Phase labels: night, day, voting, defense, waiting, finished
- Elimination messages: "[Nickname] has been eliminated"
- Win messages per faction: village, werewolves, angel, white_werewolf, pied_piper, kira, lovers
- Death messages: wolf kill, vote, hunter shot, knight curse, Kira kill, unknown cause
- Night result messages: "Tonight's deaths: ..."

lang/en/narration.php and lang/fr/narration.php:
- Per-role narrator prompt cards:
  "Wake [Role name]" / "[Role name], you may act" / "Go back to sleep"
- Bear Tamer growl/silent announcement text
- Defense window announcement text
- Scapegoat decree prompt text

lang/en/lobby.php and lang/fr/lobby.php:
- All lobby labels and instructions
- Validation error messages (all cases from LobbyService::validateGameStart)
- Difficulty setting labels
- Preset labels
- Join/create instructions

lang/en/decoys.php and lang/fr/decoys.php:
- math: 10 math puzzles with answers
- riddles: 10 riddles with answers
- counts: 5 count-the-items puzzles
- unscrambles: 10 word unscramble puzzles
- sequences: 5 number/pattern sequences

RULES:
- EVERY user-facing string must use __('file.key') syntax
- NEVER hardcode any FR or EN text in PHP or Blade files
- Locale stored in session, set on room creation
- Language toggle on welcome page: /locale/en and /locale/fr

VERIFY:
- All 27 role names translated
- All phase names translated
- All error messages translated
- Decoy puzzles are distinct and age-appropriate
- Switching locale changes all UI strings immediately
```

---

## PROMPT 19 — Railway Production Deployment

```
Read scratch.md Section 32 (Railway Deployment) fully before starting.

RAILWAY CONFIG FILES (already created in Prompt 01 — verify they are correct):
- railway.app.json: start command includes migrate --force, optimize, frankenphp
- railway.reverb.json: start command is php artisan reverb:start --host=0.0.0.0 --port=8080
- railway.queue.json: start command is php artisan queue:work --tries=3 --timeout=60

PRODUCTION .ENV TEMPLATE (.env.example):
All variables from scratch.md Section 32 must be present with placeholder values.
Critical variables:
- APP_URL=https://your-app.up.railway.app (must be https)
- REVERB_SCHEME=https
- REVERB_PORT=443
- VITE_REVERB_SCHEME=https
- VITE_REVERB_PORT=443
- SESSION_DRIVER=database
- CACHE_STORE=database
- LOG_CHANNEL=stderr

BOOTSTRAP.JS VERIFICATION:
- forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https'
- wsPort: import.meta.env.VITE_REVERB_PORT ?? 443
- wssPort: import.meta.env.VITE_REVERB_PORT ?? 443
- enabledTransports: ['ws', 'wss']
- NEVER hardcode scheme or port

CONFIG/REVERB.PHP VERIFICATION:
- host reads from REVERB_HOST env
- port reads from REVERB_PORT env (default 443)
- scheme reads from REVERB_SCHEME env (default https)
- useTLS: env('REVERB_SCHEME', 'https') === 'https'
- allowed_origins: ['*']

NGROK MIDDLEWARE:
- NgrokHeaders: local environment ONLY
- bootstrap/app.php: if (app()->environment('local')) { $middleware->prepend(NgrokHeaders::class); }
- NEVER prepended in production

MIXED CONTENT CHECKLIST:
Verify these are impossible in production:
- No HTTP asset loaded on HTTPS page
- No WS connection from WSS page
- APP_URL always https in production
- REVERB connection always wss in production
- All API calls use relative URLs (not hardcoded http://)

CADDYFILE (for FrankenPHP):
- Serves Laravel app
- Handles HTTPS termination (Railway does this externally, Caddy handles internal routing)
- php_fastcgi unix//var/run/php-fpm.sock (or appropriate)

DATABASE:
- migrations run automatically on deploy (--force flag)
- PostgreSQL connection via DATABASE_URL env var
- SESSION_DRIVER=database → php artisan session:table not needed (PostgreSQL handles it)

VERIFY ON RAILWAY:
1. App service: loads welcome page on https://
2. Reverb service logs: "Reverb server started successfully"
3. Queue service logs: "Processing jobs from the [database] queue"
4. Browser devtools Network tab: WebSocket connection is wss:// (not ws://)
5. Browser console: no mixed content warnings
6. Create room: QR code generates with https:// URL
7. Player joins via QR: WebSocket connects successfully
8. Night action submitted: NightActionSubmitted event reaches narrator dashboard
9. No CORS errors in browser console
```

---

## PROMPT 20 — Final Integration & Smoke Test

```
This is the final integration prompt. Read ALL of scratch.md before running this.

Run a full game simulation and verify every system works end-to-end:

LOBBY FLOW:
[ ] Narrator creates room → QR + code displayed
[ ] 6+ players join via QR on different devices
[ ] Narrator drags players into seat order
[ ] Narrator adds roles: 3 werewolves, 1 seer, 1 witch, villagers
[ ] Validation passes, [Start Game] enabled
[ ] Narrator starts game

ROLE ASSIGNMENT:
[ ] Every player receives role on player.{id} channel
[ ] Players can hold-to-reveal their role card
[ ] Wolf pack members know each other
[ ] Narrator sees all roles in player grid

NIGHT 1 (Mode 1 — Narrator Driven):
[ ] Cupid panel appears for Cupid, decoy for all others
[ ] Cupid links 2 players → lovers notified immediately with faction
[ ] Werewolf consensus panel: all wolves must agree, [Confirm Kill] activates on consensus
[ ] Seer inspects: binary result on player.{id}, history builds
[ ] Witch sees kill target, save/poison options work
[ ] Bear Tamer result shown in narrator prompt
[ ] All actions submitted → narrator resolves → NightResolved fires

DAY 1:
[ ] Deaths announced publicly
[ ] Defense window: [Open Defense] → [Close Defense] → final vote opens
[ ] Votes cast, slave follows master
[ ] Scapegoat handles tie correctly
[ ] Village Idiot survives vote with ban
[ ] Devoted Servant swap invisible

SUBSEQUENT NIGHTS:
[ ] White Werewolf solo kill on night 2 (even round)
[ ] Knight infection resolves night 2 before all other actions
[ ] Fox loses ability on wrong sniff
[ ] Bodyguard protection list tracked across rounds (once per player ever)
[ ] Pied Piper enchants: win check after each

KIRA:
[ ] Narrator sees Kira identity night 1
[ ] Night 2: Kira makes guess
[ ] Correct: target dies silently, guesses restored
[ ] Wrong: guess count decrements
[ ] 0 guesses: Kira dies with unknown cause announcement

MASTER:
[ ] Night 1: 2 slaves chosen, notified with master identity
[ ] Night 2: +1 slave
[ ] Slave vote locked until master votes
[ ] Master death: all slaves freed

SILENCER:
[ ] Silences correct count based on player count
[ ] Silence clears at next night transition
[ ] Narrator announces silenced players publicly at dawn

WIN CONDITIONS:
[ ] Angel wins if voted out round 1
[ ] Village wins when all wolves eliminated
[ ] Werewolves win at parity
[ ] White Werewolf wins as last alive
[ ] Pied Piper wins when all living enchanted
[ ] Kira wins on 3rd correct guess
[ ] Lovers win as last 2 alive different factions

RAILWAY PRODUCTION:
[ ] All connections use wss:// and https://
[ ] No mixed content warnings in browser console
[ ] All 3 Railway services running (app, reverb, queue)
[ ] WebSocket connects successfully from player phone to Railway Reverb
[ ] QR code points to https:// URL

REPORT any failures with the prompt number they originated from.
```

---

## Notes for OpenSpec Agent

- Feed prompts in order 01 → 20
- Do not combine multiple prompts in one run
- Each prompt assumes previous prompts are complete and passing
- When a prompt references scratch.md, read the exact section cited
- Never invent behavior not in scratch.md
- When in doubt between two approaches, choose the one that keeps controllers thinnest and events most explicit
- Mixed content (HTTP/WSS) is a hard failure — flag it immediately if encountered
- All private game data must flow through player.{id} channel only — never through room.{id}
