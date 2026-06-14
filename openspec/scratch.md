# scratch.md — Build Prompt for Loup-Garou Companion Platform (v2)

> This is a complete build specification for a clean rebuild.
> Every rule here is a binding constraint. No detail is optional.
> The AI agent must reproduce this exact architecture, feature set,
> technology stack, and design language. When in doubt, follow this file.

---

## 1. What This Project Is

A **real-life social deduction companion app** inspired by *Les Loups-Garous de Thiercelieux*.
Players are **physically together** in the same room. The app manages hidden information,
roles, narration, game state, voting, night actions, and defense windows —
while keeping human conversation at the center of the experience.

### What This Project Is NOT (do not build these)

- An online multiplayer game
- A Town of Salem clone (no automated AI narration)
- A screen-heavy experience (players should look at each other, not phones)
- A social network or profile system
- A ranked or competitive system
- Anything with progression, cosmetics, or monetization
- Anything using Ngrok or any tunnel service

---

## 2. Technology Stack

| Layer | Technology | Version |
|---|---|---|
| Backend | Laravel | ^13.7 |
| PHP | PHP | ^8.3 |
| Templating | Blade | — |
| Reactive UI | Livewire | ^4.1 |
| CSS | TailwindCSS | ^4.0.7 (CSS-first config, NO tailwind.config.js) |
| Real-time | Laravel Reverb | ^1.10 (WebSockets) |
| Build tool | Vite | ^8.0.0 with @tailwindcss/vite |
| Hosting | Railway | — (3 services: app, reverb, queue) |
| QR Code | chillerlan/php-qrcode | ^6.0 |
| JS WS client | laravel-echo ^2.3.4 + pusher-js ^8.5.0 |
| Languages | FR / EN via Laravel lang/ files |
| Database | PostgreSQL (Railway managed) / SQLite (local dev only) |
| Frontend JS | Alpine.js (bundled with Livewire) |
| Module type | ESM (type: module in package.json) |

### Do NOT introduce

- Redis, Vue, React, Pusher service, third-party auth, Flux UI components
- Ngrok or any tunnel service
- SQLite in production (Railway uses PostgreSQL)
- Serverless functions (Railway runs persistent processes)

---

## 3. Project Structure

```
lwerewolf/
├── AGENTS.md, scratch.md, composer.json, package.json, vite.config.js, phpunit.xml, pint.json, Caddyfile
├── railway.app.json, railway.reverb.json, railway.queue.json
├── app/
│   ├── Concerns/ (PasswordValidationRules, ProfileValidationRules)
│   ├── Events/ (21 event classes — all ShouldBroadcast)
│   ├── Game/
│   │   ├── Actions/ (14 action classes + ActionInterface + BaseAction)
│   │   ├── Engine/ (GameEngine, PhaseManager, ActionResolver, WinConditionChecker)
│   │   ├── Factions/ (6 faction classes + FactionInterface)
│   │   ├── Phases/ (5 phase classes + PhaseInterface)
│   │   ├── Roles/ (27 role classes + RoleInterface + BaseRole)
│   │   └── Services/ (LobbyService, ActionService, VotingService, RoleAssignmentService)
│   ├── Helpers/ (QrHelper)
│   ├── Http/Controllers/ (Controller, LobbyController, VoteController)
│   ├── Http/Middleware/ (IdentifyPlayer, NgrokHeaders)
│   ├── Livewire/Lobby/ (CreateRoom, JoinRoom)
│   ├── Livewire/Narrator/ (NarratorDashboard, NarratorLobby)
│   ├── Livewire/Player/ (PlayerGameView, PlayerLobby, NightAction, RoleCard, VotingPanel)
│   ├── Livewire/Shared/ (PlayerList)
│   ├── Models/ (Room, Player, Role, GameState, NightAction, Vote, CoupleBond)
│   └── Providers/ (AppServiceProvider)
├── config/ (app, auth, broadcasting, cache, database, livewire, reverb, session, cors, etc.)
├── database/ (PostgreSQL migrations, DatabaseSeeder, RoleSeeder)
├── lang/en/ (ui, roles, game, narration, lobby, decoys) + lang/fr/ (mirror)
├── resources/css/app.css, resources/js/{app.js, bootstrap.js}
├── resources/views/ (layouts/app, welcome, errors, components, partials, livewire/{lobby,narrator,player,shared})
├── routes/ (web.php, channels.php, console.php)
└── openspec/ (config.yaml, specs/, changes/)
```

---

## 4. Database Schema

### rooms
```sql
id BIGINT PK, code VARCHAR(6) UNIQUE, host_player_id FK->players NULLABLE,
status VARCHAR DEFAULT 'waiting', narration_mode VARCHAR DEFAULT 'human',
night_mode VARCHAR DEFAULT 'narrator_driven', settings JSON, timestamps
```

### players
```sql
id BIGINT PK, room_id FK->rooms, nickname VARCHAR, session_token VARCHAR UNIQUE,
role_id FK->roles NULLABLE, is_alive BOOL DEFAULT true, is_host BOOL DEFAULT false,
is_narrator BOOL DEFAULT false, voting_banned BOOL DEFAULT false,
is_silenced BOOL DEFAULT false, is_slave BOOL DEFAULT false,
master_id FK->players NULLABLE, seat_position INT NULLABLE, timestamps
```

### roles
```sql
id BIGINT PK, key VARCHAR UNIQUE, faction VARCHAR, night_order INT NULLABLE,
abilities JSON NULLABLE, win_condition VARCHAR, timestamps
```

### game_states
```sql
id BIGINT PK, room_id FK->rooms UNIQUE, phase VARCHAR DEFAULT 'waiting',
round INT DEFAULT 1, data JSON NULLABLE, timestamps
```

### night_actions
```sql
id BIGINT PK, game_state_id FK->game_states, player_id FK->players,
action_type VARCHAR, target_id FK->players NULLABLE, metadata JSON NULLABLE,
resolved_at TIMESTAMP NULLABLE, timestamps
```

### votes
```sql
id BIGINT PK, game_state_id FK->game_states, voter_id FK->players,
target_id FK->players, round_type VARCHAR DEFAULT 'initial', timestamps
```

### couple_bonds
```sql
id BIGINT PK, game_state_id FK->game_states, player_id FK->players,
partner_id FK->players, timestamps
```

### game_states.data JSON keys
```json
{
  "seat_order": [],
  "enchanted_player_ids": [],
  "wolf_father_used": false,
  "elder_first_attack_survived": false,
  "elder_abilities_disabled": false,
  "fox_ability_active": true,
  "bear_tamer_alive": true,
  "infected_werewolf_id": null,
  "wolf_hound_choice": null,
  "white_werewolf_solo_night": 0,
  "stuttering_judge_used": false,
  "second_vote_triggered": false,
  "pied_piper_eliminated": false,
  "vote_ban_next_round": [],
  "bodyguard_protected_ids": [],
  "bodyguard_last_protected_id": null,
  "witch_save_used": false,
  "witch_poison_used": false,
  "devoted_servant_used": false,
  "knight_killed_by_werewolf": false,
  "players_ready": [],
  "action_history": [],
  "seer_results": {},
  "fox_results": {},
  "lover_info": {},
  "last_night_deaths": [],
  "winning_faction": null,
  "scapegoat_eliminated_by_tie": false,
  "angel_eliminated_by_vote": false,
  "kira_remaining_guesses": 3,
  "kira_correct_count": 0,
  "kira_correct_targets": [],
  "master_slave_ids": [],
  "silenced_player_ids": [],
  "silencer_ability_count": 1,
  "defense_window_open": false,
  "defense_player_ids": [],
  "vote_phase": "initial"
}
```

---

## 5. Eloquent Models

- **Room**: fillable[code,host_player_id,status,narration_mode,night_mode,settings], casts[settings->array], routeKey=code, rels[players hasMany Player, host belongsTo Player(host_player_id), gameState hasOne GameState]
- **Player**: fillable[room_id,nickname,session_token,role_id,is_alive,is_host,is_narrator,voting_banned,is_silenced,is_slave,master_id,seat_position], casts[all booleans], rels[room belongsTo Room, role belongsTo Role, nightActions hasMany NightAction, votes hasMany Vote(voter_id), coupleBond hasOne CoupleBond(player_id), master belongsTo Player(master_id), slaves hasMany Player(master_id)]
- **Role**: fillable[key,faction,night_order,abilities,win_condition], casts[night_order->integer, abilities->array], rels[players hasMany Player]
- **GameState**: fillable[room_id,phase,round,data], casts[round->integer, data->array], rels[room belongsTo Room, nightActions hasMany NightAction, votes hasMany Vote, coupleBonds hasMany CoupleBond]
- **NightAction**: fillable[game_state_id,player_id,action_type,target_id,metadata,resolved_at], casts[metadata->array, resolved_at->datetime]
- **Vote**: fillable[game_state_id,voter_id,target_id,round_type], rels[gameState belongsTo GameState, voter belongsTo Player(voter_id), target belongsTo Player(target_id)]
- **CoupleBond**: fillable[game_state_id,player_id,partner_id]

---

## 6. All 27 Roles

### Village (18)

| Key | NightOrder | Abilities | WinCondition |
|---|---|---|---|
| villager | null | none | all werewolves eliminated |
| seer | 10 | inspect (binary wolf/not, private, per night, no repeat) | all werewolves eliminated |
| witch | 11 | save_potion (1 use), poison_potion (1 use) | all werewolves eliminated |
| hunter | null | last_shot (on_elimination) | all werewolves eliminated |
| bodyguard | 8 | protect (once per player, switch to decoy when all protected) | all werewolves eliminated |
| little_girl | 9 | spy (passive) | all werewolves eliminated |
| cupid | 0 | link (2 players, night 1 only, once/game) | all werewolves eliminated |
| elder | null | resilience (survive 1st wolf attack), fragility (vote out -> disable village abilities) | all werewolves eliminated |
| scapegoat | null | sacrifice (on tie), last_decree (before elimination) | all werewolves eliminated |
| village_idiot | null | revealed_innocence (vote out -> survive, lose voting) | all werewolves eliminated |
| two_sisters | null | kinship (passive, know each other at start) | all werewolves eliminated |
| three_brothers | null | kinship (passive, know each other at start) | all werewolves eliminated |
| stuttering_judge | null | second_vote (once/game, during vote phase) | all werewolves eliminated |
| knight_with_rusty_sword | null | rusty_wound (on wolf kill -> infect killer, dies start of next night) | all werewolves eliminated |
| devoted_servant | null | pre_submit_swap (vote phase start, before elimination) | all werewolves eliminated |
| bear_tamer | 14 | bear_growl (passive, morning, based on seat_order adjacency) | all werewolves eliminated |
| fox | 13 | sniff (3 adjacent by seat_order, lose ability permanently if no wolf found) | all werewolves eliminated |
| the_master | 0 | enslave (2 slaves night 1, +1 each night survived, controls vote order) | all werewolves eliminated |

### Werewolves (6)

| Key | NightOrder | Abilities | WinCondition |
|---|---|---|---|
| werewolf | 5 | group_kill (collective consensus, per night) | parity with village |
| big_bad_wolf | 6 | extra_kill (independent, if no wolf dead) | parity with village |
| accursed_wolf_father | 4 | convert (once/game, replaces kill entirely) | parity with village |
| white_werewolf | 7 | solo_kill (wolves only, even rounds only) | last standing alone |
| wolf_hound | 3 | choose_side (night 1 only, irreversible) | depends on choice |
| silencer | 2 | silence (1 player if ≤10 players, 2 if >10, lasts 1 day phase) | parity with village |

### Neutral (3)

| Key | NightOrder | Abilities | WinCondition |
|---|---|---|---|
| angel | null | divine_favor (vote out round 1 only) | eliminated by vote round 1 |
| pied_piper | 12 | enchant (one per night, win when all living enchanted) | all living enchanted |
| kira | 15 | role_guess (guess target's role, 3 guesses, restore on correct) | 3 correct role guesses |

---

## 7. Night Order (Final)

```
Night 1 only (in order):
0   Cupid (link 2 lovers)
1   Master (picks 2 initial slaves)
2   Silencer
3   Wolf Hound (faction choice — irreversible)

Every night (in order):
4   Accursed Wolf Father
5   Werewolf pack (collective kill)
6   Big Bad Wolf (extra kill if no wolf dead)
7   White Werewolf (even rounds only — solo kills wolves)
8   Bodyguard
9   Little Girl (passive — no action)
10  Seer
11  Witch
12  Pied Piper
13  Fox
14  Bear Tamer (passive — narrator reads result)
15  Master (recruits +1 slave each night after night 1)
16  Kira (always last — night 2 onwards)

Night order is configurable via narrator lobby drag-and-drop.
Default order above is the starting point.
Roles not present in the game are automatically skipped.
White Werewolf skipped on odd rounds.
Cupid, Wolf Hound, Master (initial) skipped after night 1.
Kira skipped on night 1.
```

---

## 8. Action Types (Priority Order)

| Pri | Class | Key | Behavior |
|---|---|---|---|
| 1 | CupidLinkAction | link_lovers | Round 1 only. Creates CoupleBond + notifies lovers with partner name and faction immediately. |
| 2 | MasterEnslaveAction | enslave | Night 1: 2 slaves. Night 2+: +1 per night. Slaves know master and each other. |
| 3 | SilencerSilenceAction | silence | 1 or 2 targets. Sets is_silenced=true. Clears at next night transition. |
| 4 | BodyguardProtectAction | protect | Once per player per game. Sets bodyguard_protected_id. Decoy when all alive protected. |
| 5 | WerewolfKillAction | kill | Collective consensus. Sets werewolf_kill_target_id. |
| 6 | BigBadWolfKillAction | extra_kill | Only if no wolf dead. Independent. Cannot pick same as wolf kill. |
| 7 | AccursedWolfFatherConvertAction | convert | Once/game. Replaces kill entirely. Converts target to werewolf. Notifies target + pack. |
| 8 | WhiteWerewolfKillAction | solo_kill | Even rounds only. Wolves only. Silent. |
| 9 | WitchSaveAction | save | Once/game. Cancels wolf kill on same target. |
| 10 | WitchPoisonAction | poison | Once/game. Independent kill. |
| 11 | PiedPiperEnchantAction | enchant | Adds to enchanted_player_ids. Win check after every enchant. |
| 12 | FoxInspectAction | sniff | 3 adjacent targets by seat_order. Loses ability permanently if no wolf found. |
| 13 | SeerInspectAction | inspect | Binary result: wolf or not. Cannot inspect same player twice. Fires SeerResultReady. |
| 14 | KiraGuessAction | role_guess | Night 2+. Guess target + role. Correct: target dies + 3 guesses restored. Wrong: -1 guess. 0 guesses: Kira dies silently. |

### Resolution Flow (ActionResolver::resolve)

1. Knight Rusty Sword delayed death (infected_werewolf_id) — before all else
2. Process all actions by priority
3. Wolf-Father convert replaces kill entirely that night
4. Apply deaths: cancel if bodyguard protected (blocks wolf kill + witch poison), cancel if Wolf-Father converted, cancel if Witch saved, add poison
5. Death chain: Hunter shot → lover death → loop. Win check after every death.
6. Kira wrong guess: -1 guess. Kira 0 guesses: silent death, "unknown cause" announced.
7. All actions marked resolved_at
8. NightResolved event fired
9. Silence cleared from all players (is_silenced=false, silenced_player_ids=[])

### Bodyguard blocks
- Werewolf faction kill ✓
- Witch poison ✓
- Does NOT block: White Werewolf solo kill, Big Bad Wolf extra kill, Hunter shot, Kira kill

### ActionInterface
```php
interface ActionInterface {
    public function getActingRole(): string;
    public function getTarget(): ?Player;
    public function isValid(GameState $state): bool;
    public function resolve(GameState $state): void;
    public function getPriority(): int;
}
```

BaseAction: receives NightAction model via constructor. Base isValid checks phase/night/player alive/target alive.
All actions store results in game_states.data JSON — NEVER apply effects directly to models during resolution pass.

---

## 9. Game Engine

### GameEngine
- startGame(Room) → GameState via RoleAssignmentService
- advancePhase(GameState, string) → PhaseManager
- resolveVote(GameState) → VotingService, then win check/advance
- resolveNight(GameState) → ActionResolver, then win check/advance
- eliminatePlayer(Player, GameState) → set dead, fire event, win check
- endGame(GameState, FactionInterface) → store winner, transition finished, fire GameFinished

### PhaseManager (ONLY class that changes phase)
Transitions: waiting→night, night→day|finished, day→defense|voting, defense→voting, voting→night|day|finished
On voting exit: delete votes for this round, increment round if going to night. Fires PhaseChanged.
NEVER write $state->phase directly anywhere else in the codebase.

### WinConditionChecker (priority order)
1. Angel
2. White Werewolf
3. Pied Piper
4. Kira
5. Werewolves
6. Village
7. Lovers

### Win Conditions
- **Angel**: round===1 && angel_eliminated_by_vote===true
- **White Werewolf**: exactly 1 alive player = white_werewolf
- **Pied Piper**: all alive players in enchanted_player_ids
- **Kira**: kira_correct_count===3
- **Werewolves**: werewolf_count >= village_aligned_count
- **Village**: no alive wolves (werewolf, big_bad_wolf, accursed_wolf_father, wolf_hound if chose werewolf, silencer)
- **Lovers**: exactly 2 alive, CoupleBond pair, different factions

---

## 10. Services

### LobbyService
- createRoom(nickname, locale) → Room (unique 6-char code, creates host player as narrator)
- joinRoom(Room, nickname, Request) → Player (validates status, duplicate, max 24, fires PlayerJoined)
- validateGameStart(Room) → errors[] (min 4, role count match, faction presence, Two Sisters=2, Three Brothers=3, solo max 1)

### ActionService
- submit(Player, data) → NightAction (validates narrator/alive/phase/role, checks duplicate, stores, appends action_history, fires NightActionSubmitted)

### VotingService
- submitVote(voter, target, state, round_type) → Vote (validates all rules, slave lock, fires VoteSubmitted)
- tally(state, round_type) → counts[]
- resolve(state) → winner (DB transaction, ties: Scapegoat override, no random elimination, Village Idiot spare, Elder survive, death chains, win check)
- openDefense(state) → sets defense_window_open=true, fires DefenseWindowOpened
- closeDefense(state) → sets defense_window_open=false, fires DefenseWindowClosed, opens final vote
- openFinalVote(state) → clears initial votes, sets vote_phase='final', fires FinalVoteOpened

### RoleAssignmentService
- assign(Room) → GameState (DB transaction, builds pool, shuffles, assigns, creates state with full data, fires GameStarted + RoleAssigned per player)
- Notifies Two Sisters of each other immediately
- Notifies Three Brothers of each other immediately
- Notifies Wolf pack of each other immediately
- Sets silencer_ability_count based on player count (1 if ≤10, 2 if >10)

---

## 11. Controllers (Thin Only)

- **LobbyController**: create validates→LobbyService→redirect, join validates→LobbyService→redirect
- **VoteController**: submit resolves player→VotingService→json
- **DefenseController**: open/close resolves narrator→VotingService→json

NEVER put business logic in Controllers.

---

## 12. Livewire Components

- **CreateRoom**: $nickname, submit() calls LobbyService, JS redirect
- **JoinRoom**: $code+$nickname, submit() calls LobbyService, JS redirect
- **NarratorLobby**: QR code, player list with drag seat order, role pool +/- counters, night order drag-and-drop, difficulty settings, information disclosure toggles, preset save/load, validation, start. Polls 3s.
- **NarratorDashboard**: Phase bar, player grid with badges + kick + message, night controls (mode 1 queue / mode 2 grid + force resolve), vote controls (initial → defense → final → announce), live areas (event log / action feed / relations), seat order display, game over screen. Listens to all events.
- **PlayerLobby**: Room code, player list, waiting. Redirects on GameStarted.
- **PlayerGameView**: Phase overlay, results, role card, night action/voting panels, defense awareness, game over. Listens to all room.* + player.* events.
- **NightAction**: Role-specific panel. Cupid 2-step. Wolf consensus panel. Decoy for non-acting (visually identical). Hold-to-reveal submitted. Mode 2 gate submission.
- **RoleCard**: Hold-to-reveal. Masked face (?), revealed (name/desc/faction/night_order).
- **VotingPanel**: Target list with slave lock, live tally (narrator only), master vote indicator, defense window state, confirmation, submitted reveal, ban message.
- **PlayerList**: Polls 3s, green dots, silenced badge, slave badge, enchanted badge.

---

## 13. Events (21, all ShouldBroadcast)

| Event | Channel | Data |
|---|---|---|
| AllPlayersReady | room.{id} | room_id |
| DefenseWindowClosed | room.{id} | room_id |
| DefenseWindowOpened | room.{id} | defense_player_ids[] |
| FinalVoteOpened | room.{id} | room_id |
| FoxResultReady | player.{id} | werewolf_found |
| GameFinished | room.{id} | winning_faction, winner_ids |
| GameReset | room.{id} | room_id |
| GameStarted | room.{id} | room_id |
| LoverDied | room.{id} | nickname, partner_nickname |
| LoversRevealed | player.{id} | partner_nickname, partner_faction |
| NarratorMessageSent | player.{id} | message |
| NightActionSubmitted | narrator.{room_id} | action_id, player_id, action_type, target_id |
| NightResolved | room.{id} | eliminated (nicknames[]) |
| PhaseChanged | room.{id} | phase, round |
| PlayerEliminated | room.{id} | nickname, role_key, role_name |
| PlayerJoined | room.{id} | player{id,nickname,is_narrator}, player_count |
| PlayerLeft | room.{id} | player_id, player_count |
| PlayerSilenced | room.{id} | nicknames[] |
| RoleAssigned | player.{id} | role_key, faction, night_order, abilities |
| SeerResultReady | player.{id} | target_nickname, is_werewolf (boolean) |
| SuspiciousAccessAttempt | narrator.{room_id} | player{id,nickname}, details |
| VillageIdiotRevealed | room.{id} | nickname |
| VoteSubmitted | narrator.{room_id} | voter_id, target_id, round_type |

---

## 14. WebSocket Channels

All private. Auth guard: session-token.

```php
Broadcast::channel('player.{playerId}', fn($user,$id) => $user && $user->id===(int)$id);
Broadcast::channel('narrator.{roomId}', fn($user,$id) => $user && $user->room_id===(int)$id && $user->is_narrator);
Broadcast::channel('werewolves.{roomId}', fn($user,$id) => $user && $user->room_id===(int)$id && $user->role && $user->role->faction==='werewolves');
Broadcast::channel('room.{roomId}', fn($user,$id) => $user && $user->room_id===(int)$id);
```

---

## 15. Middleware

### IdentifyPlayer
Reads session_token cookie → finds Player → merges _player onto request. Does NOT abort if missing. Applied to all web routes.

### NgrokHeaders
**LOCAL DEVELOPMENT ONLY.** Never applied in production.
```php
// bootstrap/app.php
if (app()->environment('local')) {
    $middleware->prepend(NgrokHeaders::class);
}
```

### bootstrap/app.php
```php
$middleware->validateCsrfTokens(except: ['/broadcasting/auth']);
$middleware->appendToGroup('web', IdentifyPlayer::class);
$middleware->trustProxies(at: '*', headers: X_FORWARDED_FOR|HOST|PORT|PROTO);
```

---

## 16. Authentication

No user accounts. Identity = session_token (UUID). Stored in players.session_token + httpOnly cookie.
AppServiceProvider registers session-token auth guard via auth()->viaRequest().

---

## 17. Routes

```php
Route::view('/', 'welcome');
Route::get('/locale/{locale}', fn=>redirect(home));
Route::get('/room/{room}/narrator', NarratorLobby);
Route::get('/room/{room}/player', PlayerLobby);
Route::get('/game/{room}/narrator', NarratorDashboard);
Route::get('/game/{room}/player', PlayerGameView);
Route::get('/create', CreateRoom);
Route::get('/join/{code?}', JoinRoom);
Route::post('/api/rooms', [LobbyController, 'create']);
Route::post('/api/rooms/join', [LobbyController, 'join']);
Route::post('/api/vote', [VoteController, 'submit']);
Route::post('/api/defense/open', [DefenseController, 'open']);
Route::post('/api/defense/close', [DefenseController, 'close']);
```

---

## 18. UI Design System

### Colors (app.css @theme)
```
bg-primary: #0D0D0D
bg-surface: #1A1510
bg-elevated: #251E16
text-primary: #E8D9B5
text-secondary: #9A8A6A
accent-warm: #C8922A
accent-danger: #8B2020
accent-village: #3A6B3A
accent-neutral: #5A5A8A
accent-lovers: #8B4A6B
masked-card: #000000
dead-player: #3A3530
```

### Typography
- Cinzel (serif) — headings, role names, room codes
- Inter (sans) — body text, UI

### Components (app.css)
- .phase-overlay + -night/-day/-voting/-finished — full-screen phase transitions
- .card-masked / .card-revealed — hold-to-reveal cards
- .fog-layer / .vignette — atmospheric background

### Animations
phaseOverlayIn(0.6s), fogDrift, candleFlicker, pulseGlow, elementFadeIn, bellToll

### Layout
Dark atmospheric throughout. [x-cloak]{display:none!important} for Alpine.js.

---

## 19. Localization

6 files each in lang/en/ and lang/fr/: ui.php, roles.php, game.php, narration.php, lobby.php, decoys.php

- ui.* — all UI strings
- roles.* — role names + descriptions (27 roles)
- game.* — phase labels, elimination/win messages
- narration.* — narrator prompt cards per role wake/sleep
- lobby.* — lobby labels, errors, validation
- decoys.* — night decoy puzzles (math×10, riddles×10, counts×5, unscrambles×10, sequences×5)

All strings via __('key'). NEVER hardcode FR/EN text in PHP or Blade.

---

## 20. Night Decoy System

Players with no night action see a fake puzzle (math, riddle, count, unscramble, sequence).
- Visually identical to a real night action panel
- No submission required for game state — but player must submit to check into Mode 2 gate
- "Next puzzle" refreshes client-side from lang arrays
- Never affects game state (no DB, no events)
- Narrator NEVER sees decoy activity
- Stored in lang/decoys.php arrays only

---

## 21. Role Interface Specs (Complete)

### Cupid 🏹
```
Faction: Village | Night: 1 only | Order: 0

Night 1 Panel:
→ Step 1: pick first lover (full alive player list)
→ Step 2: pick second lover (first pick locked/greyed)
→ Hold-to-confirm → panel locks "Done"

On submit — immediately:
→ Lover A receives on player.{id}:
  "You are in love with [B] — faction: [B's faction]"
→ Lover B receives on player.{id}:
  "You are in love with [A] — faction: [A's faction]"
→ Neither knows who linked them
→ Narrator Event Feed: "Cupid linked A + B"
→ Narrator Action Feed: "[Cupid's nickname] linked A + B"
→ Narrator Relations panel: "Lovers: A + B"

Night 2+: Cupid sees villager decoy panel forever.
Exactly 1 Cupid per game — lobby validation.
```

### Seer 🔮
```
Faction: Village | Night: every night | Order: 10

Night Panel:
→ "Choose a player to inspect"
→ Previously inspected players greyed out (cannot repeat)
→ Tap → hold-to-confirm

Result immediately on player.{id}:
→ "[Nickname] is a Werewolf"
→ or "[Nickname] is not a Werewolf"

Result display:
→ Persistent on screen, maskable (hold-to-reveal)
→ Accumulates night by night
→ History visible only to Seer, never transmitted publicly

Cannot inspect same player twice — enforced server-side and greyed in UI.
```

### Witch 🧪
```
Faction: Village | Night: every night | Order: 11

Night Panel (while at least 1 potion remains):
→ Shows wolf kill target: "[Nickname] will die tonight"
→ or "No kill tonight"

[Save Potion] button:
→ Greyed after use — shows "Potion used" permanently
→ Hold-to-confirm

[Poison Potion] button:
→ Opens alive player list
→ Hold-to-confirm
→ Greyed after use — shows "Potion used" permanently

Both potions spent:
→ Witch sees villager decoy panel forever (not her own greyed panel)
→ No trace of her role visible on screen

Bodyguard blocks witch poison.
Both potions independent — can use 0, 1, or 2 same night.
```

### Hunter 🏹
```
Faction: Village | Night: none (passive trigger)

Normal nights: villager decoy panel.

On elimination (any cause — vote or wolf kill):
→ Before death announced publicly
→ Hunter's screen activates:
  "You have been eliminated. Choose your target."
→ Full alive player list
→ Tap → hold-to-confirm
→ No time limit — narrator waits

On Hunter submit:
→ Target eliminated immediately
→ Death chain checks (lover? win condition?)
→ Narrator Event Feed: "Hunter shot [Nickname]"
→ Public announcement on room.{id}

Bodyguard does NOT block Hunter shot.
```

### Bodyguard 🛡️
```
Faction: Village | Night: every night | Order: 8

Constraints:
→ Cannot protect same player twice in the game (once per player, ever)
→ If all alive players already protected → villager decoy panel from that point

Night Panel:
→ Already-protected players greyed out permanently
→ Tap → hold-to-confirm

Blocks: werewolf faction kill, witch poison.
Does NOT block: White Werewolf solo kill, Big Bad Wolf extra kill, Hunter shot, Kira kill.

Silent — no notification to protected player.
Narrator Action Feed: "Bodyguard protected [Nickname]"
```

### Little Girl 👁️
```
Faction: Village | Night: passive

Normal nights: villager decoy panel.
Spying is physical (peeks eyes open in real life) — no app action.

If caught by wolves:
→ Narrator dashboard "Little Girl Caught" button
→ Immediate elimination
→ Does NOT count as wolf kill
→ Bodyguard does NOT block
→ Public announcement on room.{id}
```

### Elder 👴
```
Faction: Village | Night: none (passive)

Normal nights: villager decoy panel.

First wolf attack:
→ Elder survives silently (server-side)
→ Private on player.{id}: "You survived the wolf attack tonight"
→ Narrator Event Feed: "Elder survived first wolf attack"
→ No public announcement

Second wolf attack: Elder dies normally.

If Elder eliminated by vote:
→ elder_abilities_disabled = true
→ All village role panels switch to villager decoy from next night
→ Narrator prompt: "Elder voted out — all village abilities disabled"
→ No public announcement
```

### Scapegoat 🐐
```
Faction: Village | Night: none (passive trigger)

Normal nights: villager decoy panel.

On tied vote — before elimination announced:
→ Scapegoat's screen activates:
  "You are the scapegoat. Choose who may vote next round."
→ Assign each alive player to [Can Vote] or [Cannot Vote]
→ Hold-to-confirm → submitted

After submit:
→ Elimination announced
→ Vote bans applied next round
→ Banned players notified privately: "You cannot vote this round"
→ Narrator Action Feed: "Scapegoat banned [X, Y] from voting"
```

### Village Idiot 🤡
```
Faction: Village | Night: none (passive trigger)

Normal nights: villager decoy panel.

On vote elimination:
→ Survives — no PlayerEliminated event
→ VillageIdiotRevealed event fires
→ voting_banned = true permanently
→ Public: "[Nickname] is innocent — stays alive, loses voting rights"
→ Private on player.{id}: "Your innocence revealed. You may no longer vote."
→ Voting panel replaced with ban message
→ Narrator player card: "Idiot — No Vote" badge
```

### Two Sisters 👯‍♀️
```
Faction: Village | Night: none | Always: villager decoy

Exactly 2 in game — hard lobby validation.

On game start — immediately:
→ Each sister: "Your sister is [Nickname]"
→ No faction revealed

Narrator Relations: "Sisters: A + B"
```

### Three Brothers 👨‍👨‍👦
```
Faction: Village | Night: none | Always: villager decoy

Exactly 3 in game — hard lobby validation.

On game start — immediately:
→ Each brother: "Your brothers are [A] and [B]"
→ No faction revealed

Narrator Relations: "Brothers: A + B + C"
```

### Stuttering Judge ⚖️
```
Faction: Village | Night: none | Always: villager decoy

Once/game ability during vote phase only.

Vote Phase — Judge's screen:
→ Normal voting panel
→ Plus hidden [Request Second Vote] button (visible only to him)
→ Tap → hold-to-confirm → button disappears permanently

On submit:
→ Silent signal to narrator only
→ Narrator Dashboard prompt: "Stuttering Judge requested second vote"
→ [Trigger Second Vote] button appears on narrator vote controls
→ Narrator announces verbally, then taps button to activate
→ stuttering_judge_used = true
→ Narrator Action Feed: "Stuttering Judge requested second vote"
```

### Knight with Rusty Sword ⚔️
```
Faction: Village | Night: none (passive trigger)

Normal nights: villager decoy panel.

On wolf kill:
→ Knight dies normally, publicly announced
→ infected_werewolf_id set server-side (the wolf who attacked)
→ Infected wolf private on player.{id}:
  "You were wounded by the Knight's rusty sword. You will die at the start of next night."

Next night — before any other action:
→ Infected wolf eliminated first
→ Death chain checks run
→ Narrator Event Feed: "Knight's curse claimed [Nickname]"
→ Narrator prompt at night start: "Knight's curse — [Nickname] must die first"
→ Narrator Relations: "Infected: [Nickname]"
```

### Devoted Servant 🎭
```
Faction: Village | Night: none | Trigger: vote phase start

Once/game ability.
Normal nights: villager decoy panel.

Vote Phase — every vote phase (until ability used):
→ Devoted Servant sees normal voting panel
→ Plus pre-decision prompt at vote phase start:
  "If someone is eliminated this vote, take their place?"
→ [Yes — Sacrifice] or [No — Pass]
→ Must submit before vote closes
→ Prompt disappears forever after ability used

If pre-submitted [Yes] and someone eliminated:
→ Swap happens silently server-side BEFORE public announcement
→ Devoted Servant takes eliminated player's role
→ Eliminated player takes Devoted Servant role
→ devoted_servant_used = true
→ Public announcement reveals only final result — no swap indication
→ Narrator Event Feed: "Devoted Servant swapped with [Nickname]"
→ Narrator player grid updates with new role assignments

If [No] or already used: normal elimination proceeds. No screen reaction.
```

### Bear Tamer 🐻
```
Faction: Village | Night: passive | Order: 14

Normal nights: villager decoy panel.
No player interaction required.

Morning resolution (server-side):
→ Check Bear Tamer's left + right neighbor in seat_order
→ If either is werewolf faction → growl = true

Narrator Dashboard:
→ Seat order circular display always visible
→ Bear Tamer highlighted, neighbors marked
→ Dawn prompt: "Bear Tamer check: [Growl 🐻] or [Silent 🤫]"
→ Narrator announces verbally
→ Narrator Action Feed: "Bear growled" or "Bear was silent"

Difficulty setting: [Public growl] or [Narrator only].
```

### Fox 🦊
```
Faction: Village | Night: every night (while ability active) | Order: 13

Constraints:
→ Must pick 3 adjacent players by seat_order
→ Wrong guess → fox_ability_active = false forever
→ Once lost → villager decoy panel forever

Night Panel:
→ Selecting one player auto-selects left + right neighbor (seat_order)
→ Triple selection highlighted
→ Hold-to-confirm

Result immediately on player.{id}:
→ "A werewolf is among them"
→ or "No werewolf among them — your instincts have failed you"

On wrong guess:
→ fox_ability_active = false
→ Villager decoy from next night
→ No public announcement
→ Narrator Action Feed: "Fox sniffed [A, B, C] — [wolf found / no wolf found]"

Wolf Hound (chose wolf) + White Werewolf count as wolves for Fox.
```

### The Master 👑
```
Faction: Village | Night: night 1 (2 slaves) + every subsequent night (+1) | Order: 1 (night 1), 15 (night 2+)

Win condition: all werewolves eliminated.

Night 1 Panel:
→ "Choose your first slave" → "Choose your second slave"
→ Both from alive player list (excluding self)
→ Hold-to-confirm

Night 2+ Panel:
→ "Choose a new slave to add to your ranks"
→ Already enslaved players greyed out
→ Hold-to-confirm

On recruitment:
→ New slave on player.{id}: "You are a slave. Master: [Nickname]. Fellow slaves: [A, B...]"
→ Existing slaves on player.{id}: "New slave joins: [Nickname]"
→ Master confirmation: "Your slaves: [A, B, C...]"

Vote Phase — slave mechanics:
→ Slave voting panel locked: "Waiting for your master to vote..."
→ Once master votes → slaves receive master's choice → panel unlocks with pre-selected target
→ Slave can only confirm, not change target

Master dies:
→ All slaves on player.{id}: "Your master has fallen — you are free"
→ Slave voting panels unlock permanently

Can enslave any player (village, werewolf, neutral).
If Kira is enslaved, Kira can still kill Master (free kill for Kira).

Narrator Relations: "Master: [Nickname] → Slaves: A, B, C"
Narrator Action Feed: "Master recruited [Nickname]"
```

### Werewolf 🐺
```
Faction: Werewolf | Night: every night | Order: 5

Collective consensus kill — all wolves must agree.

Night Panel:
→ "Choose tonight's victim"
→ Full alive non-wolf player list
→ Live tally visible to all wolves:
  "Votes: [A]×2, [B]×1"
→ [Confirm Kill] activates only when all living wolves selected same target
→ Any wolf can change vote until confirmed

On game start:
→ Each wolf on player.{id}: "Your pack: [Nickname A], [Nickname B]..."

Narrator Relations: "Wolf Pack: A, B, C"
Narrator Action Feed: "Wolves target [Nickname]" (private)
```

### Big Bad Wolf 🐺💀
```
Faction: Werewolf | Night: every night (extra kill while ability active) | Order: 6

Also participates in regular wolf kill.

Extra kill available only if no wolf has died yet.
Once any wolf dies → extra kill panel never appears again.
Private on player.{id}: "A wolf has fallen — your extra kill ability is lost forever."

Night Panel (while ability active):
→ Regular wolf kill panel first (with pack)
→ Extra kill panel after pack confirms:
  "You may claim an additional victim tonight"
→ Cannot pick same target as wolf kill
→ Hold-to-confirm

Narrator Action Feed: "Big Bad Wolf extra kill: [Nickname]"
Narrator Event Feed: "Big Bad Wolf ability lost" when first wolf dies.
```

### Accursed Wolf Father 🐺👑
```
Faction: Werewolf | Night: every night | Order: 4

Once/game — conversion REPLACES wolf kill entirely that night.

Night Panel (while ability unused):
→ Regular wolf kill panel (with pack)
→ Toggle at top: [Use Convert Instead]
→ If toggled: kill panel replaced with convert panel
→ Non-wolf alive player list only
→ Toggle back available until confirmed

On convert:
→ wolf_father_used = true
→ Wolf kill cancelled entirely that night
→ Target role changed to werewolf server-side
→ Target on player.{id}: "You have been converted. You are now a Werewolf. Pack: [A, B...]"
→ Pack on player.{id}: "A new wolf joins: [Nickname]"
→ Narrator Action Feed: "Wolf Father converted [Nickname]"
→ Narrator Relations: new wolf added to pack

After ability used: regular wolf panel forever.
```

### White Werewolf 🐺⬜
```
Faction: Werewolf (solo win) | Night: every night + solo kill even rounds | Order: 7

Participates in regular wolf kill every night.
Solo kill available even rounds only (2, 4, 6...).
Solo kill targets wolves only (excluding self).

Odd nights:
→ Regular wolf kill panel only

Even nights:
→ Regular wolf kill panel first
→ Solo kill panel after pack confirms:
  "You may eliminate one of your own tonight"
→ Wolf-only alive list (excluding self)
→ Hold-to-confirm → silent, pack not notified

On solo kill:
→ Target eliminated silently
→ No public announcement until dawn
→ Narrator Action Feed: "White Werewolf solo kill: [Nickname]" (private)

Pack does NOT know about solo ability.
Win check after every solo kill.
```

### Wolf Hound 🐺🐕
```
Faction: Werewolf (or Village) | Night: 1 only | Order: 3

Night 1 Panel:
→ "You stand between two worlds. Choose your fate."
→ [Join the Village] or [Join the Pack]
→ Hold-to-confirm — irreversible warning shown
→ wolf_hound_choice stored server-side

On Village choice:
→ Treated as villager forever
→ Night 2+: villager decoy
→ Private: "You chose the village."
→ Narrator Action Feed: "Wolf Hound chose: Village"

On Werewolf choice:
→ Added to wolf pack channel immediately
→ Pack notified: "A new wolf joins: [Nickname]"
→ Wolf Hound notified of pack members
→ Night 2+: regular wolf kill panel
→ Private: "You chose the pack."
→ Narrator Action Feed: "Wolf Hound chose: Werewolf"

Not added to wolf channel until explicit choice.
Fox and Village win condition count Wolf Hound as wolf if chose werewolf.
```

### Silencer 🤫
```
Faction: Werewolf | Night: every night | Order: 2

Also participates in regular wolf kill.

Silence count:
→ 1 player per night if party ≤ 10 players
→ 2 players per night if party > 10 players
→ Set at game start in silencer_ability_count

Night Panel:
→ "Choose a player to silence tonight"
→ If count = 2: pick sequentially
→ Full alive non-wolf player list
→ Tap → hold-to-confirm

At dawn — narrator announces publicly:
→ "[Nickname] has been silenced today" (×1 or ×2)
→ Fires PlayerSilenced event on room.{id}

Silenced player on player.{id}:
→ "You have been silenced — you may not speak today"
→ Silence badge visible on their screen all day phase

Silence clears automatically at night transition:
→ is_silenced = false for all players
→ silenced_player_ids = []

Can silence any player regardless of faction.

Difficulty setting (narrator-controlled per game):
→ Default: silenced players CAN still vote
→ Optional: "Silence includes vote ban"

Narrator Dashboard:
→ Action Feed: "Silencer silenced [A] (and [B])"
→ Player cards show silence 🤫 badge during day
→ Silence clears automatically shown in Event Feed at night start
```

### Angel 😇
```
Faction: Neutral | Night: none | Always: villager decoy

Win condition: eliminated by village vote in round 1 ONLY.
Wolf kill in round 1 does NOT trigger win.

On vote elimination round 1:
→ WinConditionChecker fires → Angel wins alone
→ GameFinished event → angel win screen

If Angel survives round 1 vote:
→ Private on player.{id}: "Your divine window has passed. You now fight for the village."
→ Angel treated as villager from round 2
→ Narrator Action Feed: "Angel joins village — divine favor expired"

If Angel killed by wolves round 1: normal death, no win.
```

### Pied Piper 🎵
```
Faction: Neutral | Night: every night | Order: 12

One enchant per night.
Cannot enchant already enchanted players.
Cannot enchant himself.
Win check runs after every enchant.

Night Panel:
→ "Choose a player to enchant tonight"
→ Already enchanted players greyed out
→ Self excluded
→ Tap → hold-to-confirm

On enchant:
→ Target added to enchanted_player_ids
→ Target on player.{id}: "You feel an irresistible melody... you are enchanted 🎵"
→ No identity of Pied Piper revealed to target
→ Enchanted badge on target's screen — maskable

Win condition:
→ All living players in enchanted_player_ids
→ Pied Piper wins alone

Narrator Action Feed: "Pied Piper enchanted [Nickname]"
Narrator Relations: "Enchanted: A, B, C..."
```

### Kira ⚔️
```
Faction: Neutral | Night: night 2+ | Order: 16 (always last)

Win condition: correctly guess 3 different players' roles.
Must guess roles other than villager.
Silent death if guesses exhausted.

Guess Economy:
→ Starts with 3 guesses (kira_remaining_guesses = 3)
→ Correct guess → kira_correct_count + 1 + restore to 3 guesses
→ Wrong guess → kira_remaining_guesses - 1
→ 0 guesses remaining → Kira eliminated silently
→ Death announced as "unknown cause" (configurable: fully hidden)

Night 1:
→ Narrator privately notified who Kira is
→ Narrator Action Feed: "Kira is [Nickname]"
→ Kira sees villager decoy panel night 1

Night 2+ Panel:
→ "Do you want to target someone tonight?"
→ [Yes — Make a Guess] or [Skip Tonight]

On [Yes]:
→ Step 1: pick alive target
→ Step 2: pick role from full role list (villager excluded)
→ Hold-to-confirm

On correct guess:
→ Target eliminated silently
→ Narrator announces: "unknown cause of death" (or hidden per difficulty)
→ Kira on player.{id}: "Correct — [Nickname] was [Role]. Guesses restored to 3."
→ kira_correct_count + 1
→ Narrator Action Feed: "Kira correctly guessed [Nickname] as [Role]"

On wrong guess:
→ Target survives — no public announcement
→ Kira on player.{id}: "Wrong — [X] guesses remaining"
→ Narrator Action Feed: "Kira wrong guess — [X] guesses remaining"

On 0 guesses:
→ Kira eliminated silently
→ Public: "unknown cause of death"
→ Narrator Action Feed: "Kira eliminated — guesses exhausted"

On 3 correct guesses:
→ kira_correct_count === 3 → Kira wins alone
→ GameFinished event

Narrator Dashboard:
→ Player card shows Kira's current guess count badge
→ Difficulty: [Unknown death] or [Hidden completely]
```

---

## 22. Role Image System

### Overview
```
Role cards support optional custom images per role.
Images are loaded from a fixed public path by role_key.
If no image exists for a role, a generic silhouette SVG is shown instead.
Images are ONLY shown on the revealed state of the role card (after hold-to-reveal).
The masked state (before reveal) always shows the atmospheric card face — never an image.
```

### File Convention
```
Standard (phone):   public/images/roles/{role_key}.png      — 400×560px
Retina/tablet:      public/images/roles/{role_key}@2x.png   — 800×1120px

Examples:
  public/images/roles/seer.png          (400×560px)
  public/images/roles/seer@2x.png       (800×1120px)
  public/images/roles/werewolf.png
  public/images/roles/werewolf@2x.png
  public/images/roles/kira.png
  public/images/roles/kira@2x.png
  ... (up to 54 files total — 27 roles × 2 resolutions)

@2x is optional — if missing, browser uses @1x automatically.
Both are optional — if neither exists, placeholder.svg is used.

role_key values (exact, matches roles.key in DB):
  villager, seer, witch, hunter, bodyguard, little_girl, cupid,
  elder, scapegoat, village_idiot, two_sisters, three_brothers,
  stuttering_judge, knight_with_rusty_sword, devoted_servant,
  bear_tamer, fox, the_master, werewolf, big_bad_wolf,
  accursed_wolf_father, white_werewolf, wolf_hound, silencer,
  angel, pied_piper, kira
```

### Fallback Silhouette SVG
```
Location: public/images/roles/placeholder.svg
Content: generic atmospheric humanoid silhouette
ViewBox: 0 0 400 560 (matches 400×560px card ratio exactly)
Style: accent-warm #C8922A stroke on transparent background
Same SVG used for ALL roles that have no image yet
SVG scales perfectly to any display size (vector)
```

### Blade Helper Logic
```php
// In Blade/Livewire — check if role image exists, fall back to placeholder
$roleKey = $role->key;
$imagePath1x = public_path("images/roles/{$roleKey}.png");
$imagePath2x = public_path("images/roles/{$roleKey}@2x.png");

$has1x = file_exists($imagePath1x);
$has2x = file_exists($imagePath2x);

$roleImageSrc    = $has1x
    ? asset("images/roles/{$roleKey}.png")
    : asset('images/roles/placeholder.svg');

$roleImageSrcset = $has1x
    ? asset("images/roles/{$roleKey}.png") . ' 1x' .
      ($has2x ? ', ' . asset("images/roles/{$roleKey}@2x.png") . ' 2x' : '')
    : null;

// If no image:  <img src="placeholder.svg"> — no srcset
// If 1x only:  <img src="...png" srcset="...png 1x">
// If both:     <img src="...png" srcset="...png 1x, ...@2x.png 2x">
```

### Role Card — Revealed State Layout
```
card-revealed state (after hold-to-reveal):

┌─────────────────────────────┐
│                             │
│   [Role image as full       │
│    card background]         │
│   with dark overlay         │
│   gradient at bottom        │
│                             │
│▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓│
│  ROLE NAME (Cinzel)         │
│  Faction label              │
│  Night order (if any)       │
│  Abilities description      │
└─────────────────────────────┘

CSS structure:
→ Card: position relative, overflow hidden
→ Image: position absolute, inset 0, object-fit cover, z-index 0
→ Gradient overlay: position absolute, inset 0,
  background: linear-gradient(to top, rgba(0,0,0,0.92) 40%, rgba(0,0,0,0.3) 100%)
  z-index 1
→ Text content: position relative, z-index 2, bottom-aligned padding

Masked state: no image shown, only atmospheric card face (.card-masked)
```

### Image Dimensions & Format
```
Standard (phone):  400×560px PNG (portrait, 5:7 ratio)
Retina/tablet:     800×1120px PNG (@2x — same filename, use srcset)

Target display size on card: ~280×392px CSS (scales with viewport)
Physical file: 400×560px minimum — crisp on phones
Tablet (@2x): 800×1120px — crisp on tablets and high-DPI screens

Recommended workflow:
- Create artwork at 800×1120px
- Export @1x (400×560px) as {role_key}.png
- Export @2x (800×1120px) as {role_key}@2x.png
- Blade srcset handles resolution switching automatically

Blade srcset pattern:
<img
  src="{{ asset("images/roles/{$roleKey}.png") }}"
  srcset="{{ asset("images/roles/{$roleKey}.png") }} 1x,
          {{ asset("images/roles/{$roleKey}@2x.png") }} 2x"
  alt="{{ $roleName }}"
  class="absolute inset-0 w-full h-full object-cover object-top z-0"
/>

If @2x file missing: browser falls back to @1x automatically.
If both missing: placeholder.svg shown instead.

Placeholder SVG dimensions: viewBox="0 0 400 560" (same ratio)
```

### Adding Images Later
```
To add a role image:
1. Place files at:
   public/images/roles/{role_key}.png      (400×560px — phone)
   public/images/roles/{role_key}@2x.png   (800×1120px — tablet/retina)
2. No code changes needed — Blade helper auto-detects both
3. Deploy: push to GitHub → Railway auto-deploys

No migrations, no DB changes, no seeder updates needed.
Images are purely static assets.
```

### Directory Structure to Create at Scaffold
```
public/
└── images/
    └── roles/
        └── placeholder.svg   ← created at scaffold time
        (all {role_key}.png files added later by developer)
```

---

## 23. Narrator Dashboard — Complete Spec

### Lobby Phase

```
Join Panel:
→ Large QR code (QrHelper::generate)
→ Room code in Cinzel font underneath
→ Live player list (updates via PlayerJoined event)
→ Each player row: nickname + joined time + drag handle (seat order) + [Kick]

Seat Order:
→ Narrator drags players into seat positions (circular)
→ Circular seating preview updates live
→ Locked when game starts
→ Stored in seat_order and players.seat_position

Role Composition Panel:
→ Role pool organized by faction: Village / Werewolf / Neutral
→ Each role: name, description tooltip, +/- counter
→ Hard validations enforced live:
  - Two Sisters: exactly 0 or 2
  - Three Brothers: exactly 0 or 3
  - Solo roles: max 1 each
  - Total roles must equal player count
→ Validation errors inline
→ [Start Game] locked until all validations pass

Night Order Panel:
→ Drag-and-drop role sequence
→ Default order pre-loaded (see Section 8)
→ Only active roles shown
→ Each card: night_order number + role name
→ [Reset to Default] button

Difficulty Settings Panel:
→ Night Mode: [Narrator-Driven] / [Simultaneous]
→ Silencer: [Allow vote ban] toggle (default: off)
→ Bear Tamer: [Public growl] / [Narrator only] toggle
→ Kira: [Unknown death] / [Hidden completely] toggle

Information Disclosure Panel:
→ Toggle what players see before game starts
→ Per faction: Village / Werewolf / Neutral roles shown/hidden
→ Or per individual role

Presets:
→ [Save current as preset] → name it → stored locally
→ [Load preset] dropdown
→ Always editable after loading
→ Auto-suggest recommended composition per player count

[Start Game]:
→ Locked until validations pass
→ Triggers RoleAssignmentService
→ Redirects narrator to game dashboard
```

### Game Dashboard — Layout

```
┌─────────────────────────────────────────┐
│  PHASE BAR (top)                        │
├──────────────┬──────────────────────────┤
│              │                          │
│  PLAYER GRID │   LIVE AREAS (3 tabs)    │
│  (left)      │   Event / Action /       │
│              │   Relations              │
│              │                          │
├──────────────┴──────────────────────────┤
│  NIGHT / VOTE CONTROLS (bottom)         │
└─────────────────────────────────────────┘
```

### Phase Bar

```
→ Current phase label: NIGHT / DAY / VOTING
→ Current round number
→ [Advance Phase] button — narrator-triggered always
→ Smart prompts (appear when conditions met, narrator still decides):
  "All night actions submitted — ready to resolve"
  "All votes in — ready to close voting"
  "Stuttering Judge requested second vote"
  "Knight's curse — [Nickname] must die first"
  "Devoted Servant must decide — waiting"
  "Bear Tamer check: Growl 🐻 or Silent 🤫"
  "Kira eliminated — unknown cause to announce"
  "Elder voted out — village abilities disabled"
→ Prompts dismissible
```

### Player Grid

```
Each player card:
→ Nickname
→ Role name + faction color border:
  Village: #3A6B3A | Werewolf: #8B2020 | Neutral: #5A5A8A | Lovers: #8B4A6B
→ Status: Alive (green dot) / Dead (greyed) / Disconnected (orange dot)
→ Mode 2 badge: Submitted ✓ / Pending ⏳
→ Special badges:
  Silenced 🤫 / Slave 👑 / Enchanted 🎵 / Infected ☠️ /
  No Vote 🚫 / Idiot 🤡 / Kira guesses [X]
→ Action buttons:
  [Kick] — removes player, slot reopens
  [Send Message] — private on player.{id}

Contextual narrator buttons:
→ [Little Girl Caught] — during wolf phase only
→ [Trigger Second Vote] — after Judge signals
→ [Force Resolve] — Mode 2 night only
```

### Live Areas — 3 Tabs

```
Tab 1 — Event Log:
→ Timestamped chronological feed
→ Phase changes, deaths, eliminations, wins,
  disconnections, kicks, ability uses, silences
→ Color coded by event type
→ Newest at top

Tab 2 — Action Feed (narrator only):
→ "[Role/Nickname] → [Action] → [Target]"
→ Decoy submissions marked [DECOY]
→ Pending: muted | Resolved: full color
→ Newest at top

Tab 3 — Relations:
→ Lovers 💕 → [A] + [B] (factions shown)
→ Wolf Pack 🐺 → [A], [B], [C]
  (Wolf Hound added only after choice)
  (Converted players added immediately)
→ Enchanted 🎵 → [A], [B], [C]...
→ Slaves 👑 → Master: [X] → Slaves: [A, B, C]
→ Infected ☠️ → [Nickname] — dies next night
→ Sisters 👯 → [A] + [B]
→ Brothers 👨‍👨‍👦 → [A] + [B] + [C]

Seat Order (below relations, always visible):
→ Circular: A — B — C — D ... — A
→ Bear Tamer highlighted, neighbors marked
→ Dead players greyed but kept in position
→ Fox sniff adjacency shown when Fox acts
```

### Night Controls

```
Mode 1 — Narrator Driven:
→ Wake order queue (configured night order)
→ Current role card highlighted: "Wake [Role]"
→ [Mark Done] advances to next
→ Auto-skips: absent roles, White Werewolf odd rounds,
  Cupid/Wolf Hound/Master(initial) after night 1,
  Kira night 1, Fox when ability lost,
  Bodyguard when all protected

Mode 2 — Simultaneous:
→ Full submission grid: every player + submitted/pending
→ Narrator sees exactly who has not submitted
→ [Force Resolve] button (narrator override)
→ No auto-timeout — narrator decides entirely

Both modes after resolution:
→ Death summary: "Tonight's deaths: [A], [B]"
→ Narrator confirms privately before public announcement
→ [Announce Deaths] → fires NightResolved event
```

### Vote Controls (Full Flow)

```
Phase 1 — Initial Vote:
→ Voting opens for all eligible players
→ Silenced: can vote by default (ban is difficulty setting)
→ Slaves: locked until master votes
→ Narrator sees full voter breakdown privately
→ [Close Initial Vote] — narrator triggered

Phase 2 — Defense Window:
→ [Open Defense] button appears
→ Narrator chooses who speaks (verbally)
→ No app interaction during defense — physical/verbal only
→ Dashboard shows: "Defense in progress"
→ [Close Defense] — narrator triggered → final vote opens automatically

Phase 3 — Final Vote:
→ All previous votes cleared
→ Everyone votes again from scratch
→ Live tally updates on narrator dashboard
→ [Close Final Vote] — narrator triggered

Phase 4 — Resolution:
→ Final tally locked
→ Tie → Scapegoat prompt / second vote
→ Village Idiot voted out → survive prompt
→ Devoted Servant → pre-submitted decision applies silently
→ Stuttering Judge request → [Trigger Second Vote] appears
→ [Announce Elimination] → fires events

Narrator Vote Control Sequence:
[Close Initial Vote] → [Open Defense] → [Close Defense]
→ [Final Vote auto-opens] → [Close Final Vote]
→ [Announce Elimination]
```

### Game Over Screen

```
→ Winning faction prominent
→ Winner list with roles
→ Full role reveal: every player + their role
→ Game summary: rounds, eliminations, key events
→ [Start New Game]:
  - Clears: game_states, night_actions, votes, couple_bonds
  - Resets: all player records (role_id, is_alive, voting_banned, is_silenced, is_slave, master_id)
  - Returns to lobby with same players
  - Role composition preserved as starting point
→ [End Session] — dissolves room entirely
```

---

## 24. Player Interface — Complete Spec

### Default State (day/waiting)
```
→ Minimal atmospheric screen
→ Room code, round number, phase label
→ Their own alive/dead status
→ Hold-to-reveal role card access
→ If silenced: "🤫 You have been silenced today"
→ If enchanted: "🎵 You are enchanted" (maskable)
→ If slave: "👑 Wait for your master to vote"
```

### Night Phase (all players)
```
→ Decoy panel appears immediately for everyone
→ Visually identical to real action panel
→ Non-acting players submit decoy to check into Mode 2 gate
→ "Next puzzle" refreshes decoy client-side
→ No screen change for acting players in Mode 1
  (narrator calls verbally, panel already on screen)
```

### Vote Phase
```
→ Voting panel activates
→ Slaves: locked until master submits
→ Banned: "You cannot vote this round" message
→ Silenced (with ban difficulty): cannot vote
→ Stuttering Judge: hidden [Request Second Vote] button
→ Devoted Servant: pre-decision prompt at phase start
```

### Key Screen Triggers
```
Game start → role card reveal (hold-to-reveal)
Night begins → decoy panel for everyone
Acting night role → panel already showing (Mode 1: no change)
Private result (Seer/Fox/Kira) → persistent maskable display
Lover notification → immediate on Cupid submit
Slave notification → immediate on Master recruit
Enchanted notification → immediate on Pied Piper enchant
Vote phase → voting panel
Defense window → "Defense in progress — [Narrator is speaking]"
Elimination → result shown publicly
Game over → faction win screen
```

---

## 25. Edge Cases (Locked Decisions)

- **Tie vote**: Scapegoat submits decree first, then eliminated. Second vote among tied only. Still tied → no elimination. NEVER random.
- **Werewolf kill**: Narrator-driven consensus. No timer. [Confirm Kill] only when all agree.
- **Disconnect**: 2-min reconnect window. Force-kill if no reconnect. WinConditionChecker runs. No death chain effects on disconnect death.
- **Little Girl Caught**: Narrator dashboard button. Immediate elimination. NOT wolf kill. Bodyguard does NOT block.
- **Village Idiot vote-out**: Survives. voting_banned=true. Role publicly revealed. No PlayerEliminated event.
- **Devoted Servant swap**: Pre-submitted at vote phase START. Applied BEFORE role reveal. Swap prompt never visible to others.
- **Scapegoat last decree**: Submitted BEFORE elimination announced.
- **White Werewolf cadence**: Night 1 no, Night 2 yes, Night 3 no, Night 4 yes (even rounds).
- **Knight Rusty Sword**: Infection set night killed. Infected wolf dies START of NEXT night, before all other actions.
- **Fox loses ability permanently** on wrong sniff. No recovery. Wolf Hound (chose wolf) + White Werewolf count as wolves for Fox.
- **Elder vote-out**: elder_abilities_disabled=true. ALL village abilities check this flag. Village panels switch to decoy.
- **Wolf Hound**: Faction set at runtime night 1. Not before. Not added to wolf channel until explicit choice.
- **Pied Piper win check** runs after every enchant, not just eliminations.
- **Angel win**: ONLY vote elimination round 1. Wolf kill round 1 does NOT trigger.
- **Bear Tamer growl**: Based on seat_order adjacency, not role proximity.
- **Two Sisters**: Exactly 2. **Three Brothers**: Exactly 3. Hard lobby validation.
- **Bodyguard**: Blocks werewolf faction kill + witch poison only. Not White Werewolf solo, BBW extra, Hunter shot, Kira kill.
- **Witch save+poison**: Both optional, independent. Save evaluated before poison. Kill target shown to Witch first.
- **Kira**: Cannot guess 'villager'. Silent death on 0 guesses. Kira can kill Master even if enslaved.
- **Silencer**: Clears every night. Can silence any player any faction. Cannot silence same player twice in a row.
- **Master**: Slaves must confirm master's vote, cannot change target. Freed on master death.
- **New Game**: Reuse existing room. Clear game_states/night_actions/votes/couple_bonds. Reset player records. NEVER create new room.
- **Mixed content**: NEVER mix HTTP and HTTPS. ALL connections (app + reverb) must use the same scheme. Railway enforces HTTPS/WSS — no exceptions.

---

## 26. QrHelper

```php
class QrHelper {
    public static function generate(string $data): string {
        $options = new QROptions([
            'svgAddXmlHeader' => false,
            'eccLevel' => EccLevel::M,
            'scale' => 8
        ]);
        return (new QRCode($options))->render($data);
    }
}
```

---

## 27. AppServiceProvider

```php
public function boot(): void {
    Date::use(CarbonImmutable::class);
    DB::prohibitDestructiveCommands(app()->isProduction());

    $locale = session('locale');
    if ($locale && in_array($locale, ['en', 'fr'])) {
        app()->setLocale($locale);
    }

    auth()->viaRequest('session-token', function ($request) {
        $token = $request->cookie('session_token');
        return $token ? Player::where('session_token', $token)->first() : null;
    });
}
```

---

## 28. Bootstrap JS (WebSocket Setup)

```js
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
});
```

---

## 29. Config Files

### config/auth.php
```php
'guards' => [
    'web' => ['driver' => 'session', 'provider' => 'users'],
    'session-token' => ['driver' => 'session-token'],
],
```

### config/reverb.php
```php
'servers' => ['reverb' => ['host' => '0.0.0.0', 'port' => env('REVERB_SERVER_PORT', 8080)]],
'apps' => ['provider' => 'config', 'apps' => [[
    'key' => env('REVERB_APP_KEY'),
    'secret' => env('REVERB_APP_SECRET'),
    'app_id' => env('REVERB_APP_ID'),
    'options' => [
        'host' => env('REVERB_HOST'),
        'port' => env('REVERB_PORT', 443),
        'scheme' => env('REVERB_SCHEME', 'https'),
        'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
    ],
    'allowed_origins' => ['*'],
    'ping_interval' => 60,
]]],
```

### config/livewire.php key settings
```php
'inject_assets' => true,
'navigate' => ['show_progress_bar' => true, 'progress_bar_color' => '#C8922A'],
'payload' => ['max_size' => 1024*1024, 'max_nesting_depth' => 10, 'max_calls' => 50, 'max_components' => 200],
```

---

## 30. vite.config.js

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({ inputs: ['resources/css/app.css', 'resources/js/app.js'] }),
        tailwindcss(),
    ],
    server: { cors: true, ignore: ['storage/framework/views'] },
});
```

---

## 31. Base Layout (layouts/app.blade.php)

```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#0D0D0D] text-[#E8D9B5] antialiased min-h-screen font-sans">
    <div class="fog-layer"></div>
    <div class="vignette"></div>
    <div class="min-h-screen flex flex-col relative z-10">
        <main class="flex-1">{{ $slot ?? '' }}@yield('content', '')</main>
    </div>
    @livewireScripts
</body>
</html>
```

---

## 32. CSS Theme (resources/css/app.css)

```css
@import 'tailwindcss';
@source '../views';
@custom-variant dark (&:where(.dark, .dark *));

@theme {
    --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
    --font-serif: 'Cinzel', ui-serif, Georgia, Cambria, 'Times New Roman', Times, serif;
    --color-bg-primary: #0D0D0D;
    --color-bg-surface: #1A1510;
    --color-bg-elevated: #251E16;
    --color-text-primary: #E8D9B5;
    --color-text-secondary: #9A8A6A;
    --color-accent-warm: #C8922A;
    --color-accent-danger: #8B2020;
    --color-accent-village: #3A6B3A;
    --color-accent-neutral: #5A5A8A;
    --color-accent-lovers: #8B4A6B;
    --color-masked-card: #000000;
    --color-dead-player: #3A3530;
}

@layer components {
    .phase-overlay { @apply fixed inset-0 z-50 flex items-center justify-center; animation: phaseOverlayIn 0.6s ease-in-out forwards; }
    .phase-overlay-night { background: radial-gradient(ellipse at center, #0D0D0D 0%, #000000 70%); }
    .phase-overlay-day { background: radial-gradient(ellipse at center, #2A2015 0%, #0D0D0D 70%); }
    .phase-overlay-voting { background: radial-gradient(ellipse at center, #3A1A1A 0%, #0D0D0D 70%); }
    .phase-overlay-finished { background: radial-gradient(ellipse at center, #C8922A 0%, #1A1510 70%); }
    .card-masked { @apply border-2 border-[#C8922A]/30; background: linear-gradient(135deg, #1A1510 0%, #0D0A07 100%); }
    .card-revealed { @apply border-2 border-[#C8922A]; background: linear-gradient(135deg, #2A2015 0%, #1A1510 100%); }
    .fog-layer { position:fixed; inset:0; pointer-events:none; z-index:0; background: radial-gradient(ellipse at 50% 100%, rgba(200,146,42,0.03) 0%, transparent 60%); }
    .vignette { position:fixed; inset:0; pointer-events:none; z-index:1; background: radial-gradient(ellipse at center, transparent 50%, rgba(0,0,0,0.6) 100%); }
}

@keyframes phaseOverlayIn { 0%{opacity:0} 30%{opacity:1} 70%{opacity:1} 100%{opacity:0} }
@keyframes fogDrift { 0%{transform:translateX(0) translateY(0);opacity:0.3} 50%{opacity:0.6} 100%{transform:translateX(-5%) translateY(-2%);opacity:0.2} }
@keyframes candleFlicker { 0%,100%{opacity:1} 50%{opacity:0.7} 25%,75%{opacity:0.85} }
@keyframes pulseGlow { 0%,100%{box-shadow:0 0 5px rgba(200,146,42,0.2)} 50%{box-shadow:0 0 15px rgba(200,146,42,0.4)} }
@keyframes elementFadeIn { from{opacity:0;transform:translateY(4px)} to{opacity:1;transform:translateY(0)} }
@keyframes bellToll { 0%{transform:scale(0.5);opacity:0} 20%{transform:scale(1.2);opacity:1} 40%{transform:scale(0.9);opacity:1} 60%{transform:scale(1.05);opacity:0.8} 100%{transform:scale(1);opacity:0} }
[x-cloak] { display: none !important; }
```

---

## 33. Railway Deployment

### Project Structure on Railway
```
Railway Project: lwerewolf
├── Service: app      (web — Laravel + FrankenPHP)
├── Service: reverb   (WebSocket — php artisan reverb:start)
├── Service: queue    (Worker — php artisan queue:work)
└── Database: PostgreSQL (Railway managed, auto-injects DATABASE_URL)
```

### Railway Config Files (at project root)

**railway.app.json**
```json
{
  "$schema": "https://railway.com/railway.schema.json",
  "build": { "builder": "RAILPACK" },
  "deploy": {
    "startCommand": "php artisan migrate --force && php artisan optimize && frankenphp run --config /etc/caddy/Caddyfile",
    "healthcheckPath": "/",
    "healthcheckTimeout": 30,
    "restartPolicyType": "ON_FAILURE"
  }
}
```

**railway.reverb.json**
```json
{
  "$schema": "https://railway.com/railway.schema.json",
  "build": { "builder": "RAILPACK" },
  "deploy": {
    "startCommand": "php artisan reverb:start --host=0.0.0.0 --port=8080",
    "restartPolicyType": "ALWAYS"
  }
}
```

**railway.queue.json**
```json
{
  "$schema": "https://railway.com/railway.schema.json",
  "build": { "builder": "RAILPACK" },
  "deploy": {
    "startCommand": "php artisan queue:work --tries=3 --timeout=60",
    "restartPolicyType": "ALWAYS"
  }
}
```

### Environment Variables (shared across all 3 services)
```env
APP_NAME=LoupGarou
APP_ENV=production
APP_KEY=base64:... (generate with php artisan key:generate --show)
APP_DEBUG=false
APP_URL=https://lwerewolf.up.railway.app

DB_CONNECTION=pgsql
DATABASE_URL=${DATABASE_URL}

BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
LOG_CHANNEL=stderr

REVERB_APP_ID=lwerewolf
REVERB_APP_KEY=your-key
REVERB_APP_SECRET=your-secret
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
REVERB_HOST=lwerewolf-reverb.up.railway.app
REVERB_PORT=443
REVERB_SCHEME=https

VITE_REVERB_APP_KEY=your-key
VITE_REVERB_HOST=lwerewolf-reverb.up.railway.app
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

### Critical: No Mixed Content
```
ALL connections must use HTTPS/WSS.
APP_URL must be https://
REVERB_SCHEME must be https
VITE_REVERB_SCHEME must be https
forceTLS must be true in bootstrap.js when scheme is https
Railway handles TLS termination — Reverb runs plain WS internally on port 8080,
Railway proxies externally as WSS on port 443.
NEVER serve the app on HTTP and Reverb on WS in production.
```

### Railway Setup Steps
```
1. Push code to GitHub (with all 3 railway.*.json files)

2. Railway → New Project → Empty Project

3. Add PostgreSQL:
   → Create → Database → PostgreSQL
   → DATABASE_URL auto-injected into all services

4. Add App service:
   → Create → Empty Service → connect GitHub repo
   → Name: "app"
   → Variables: paste all env vars
   → Settings → Builder: Railpack
   → Settings → Config file: railway.app.json
   → Generate domain → set as APP_URL

5. Add Reverb service:
   → Duplicate app service
   → Name: "reverb"
   → Settings → Config file: railway.reverb.json
   → Generate domain → set as REVERB_HOST (no https://)
   → Railway service port: 8080 (internal — Reverb listens here)
   → REVERB_PORT env var: 443 (external — Railway proxies WSS here)
   → VITE_REVERB_PORT env var: 443 (what the browser connects to)

6. Add Queue service:
   → Duplicate app service
   → Name: "queue"
   → Settings → Config file: railway.queue.json
   → No public domain needed

7. Redeploy all 3 services after env vars confirmed

8. Verify:
   → App: APP_URL loads welcome page
   → Reverb logs: "Reverb server started"
   → Queue logs: "Processing jobs from database queue"
   → Create room, scan QR, confirm WebSocket connects (wss://)
```

### Local Development
```
Terminal 1: php artisan serve --host=0.0.0.0 --port=8000
Terminal 2: php artisan reverb:start
Terminal 3: php artisan queue:work
Terminal 4: npm run dev

.env.local:
DB_CONNECTION=sqlite
APP_URL=http://localhost:8000
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

---

## 34. Key Architecture Rules (MUST follow — all binding)

1. **Controllers are thin** — receive request, call Service, return response. NEVER game logic.
2. **PhaseManager is the ONLY class that changes phase** — NEVER write $state->phase directly elsewhere.
3. **Actions are never resolved on submission** — store pending, resolved_at=null. Resolution only in ActionResolver::resolve().
4. **Never broadcast directly** — fire Events with ShouldBroadcast. Never Broadcast::channel() from controllers/services.
5. **Sensitive data only on private player channels** — roles, night results, lover identity, slave info, Kira results on player.{id} only.
6. **WinConditionChecker runs after every elimination** — after PlayerEliminated and vote resolution and enchant.
7. **No hardcoded role logic in Engine** — role behavior in role/action classes. Engine calls interfaces.
8. **All user-facing strings through lang files** — NEVER hardcode FR/EN text.
9. **Death chains fully resolve before WinConditionChecker** — Lover death, Hunter shot, Knight infection all complete first.
10. **Narrator is never a player** — no role_id, not in player lists, cannot vote/act, narrator dashboard only.
11. **Every request verified against ownership** — session_token resolves Player, verify room_id match, verify role/permission.
12. **No mixed content ever** — APP_URL and REVERB must use same scheme. Production: always HTTPS/WSS.
13. **NgrokHeaders middleware: local only** — never applied in production.
14. **SQLite: local dev only** — production always PostgreSQL on Railway.
15. **Defense window is narrator-controlled** — opens and closes manually. Never auto-advances.
16. **Silence clears automatically at night transition** — never persists across phases.
17. **Devoted Servant swap is invisible** — pre-submitted, applied before announcement, no UI tells.
18. **Seat order is set in lobby and locked at game start** — never changes mid-game.

---

## 35. Setup Instructions

### Local Development
```bash
# 1. Create Laravel project
composer create-project laravel/laravel lwerewolf
cd lwerewolf

# 2. Install dependencies
composer require livewire/livewire laravel/reverb chillerlan/php-qrcode
npm install tailwindcss @tailwindcss/vite laravel-echo pusher-js axios

# 3. Configure .env for local
DB_CONNECTION=sqlite
BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database
APP_URL=http://localhost:8000
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http

# 4. Run migrations and seeder
php artisan migrate
php artisan db:seed

# 5. Build frontend
npm run build

# 6. Start services (4 terminals)
php artisan serve --host=0.0.0.0 --port=8000
php artisan reverb:start
php artisan queue:work
npm run dev
```

### Production (Railway)
```bash
# Push to GitHub → Railway auto-deploys
# Follow Section 32 Railway Setup Steps
# Verify all 3 services running
# Verify wss:// connection in browser devtools Network tab
```
