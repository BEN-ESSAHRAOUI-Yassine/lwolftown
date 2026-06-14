# WORKFLOW.md — lwerewolf OpenSpec Build Guide

> This file lives in openspec/ alongside scratch.md, config.yaml, and prompts.md.
> Read this before touching anything. Follow it exactly.
> The goal: one clean feature at a time, never build on broken ground.

---

## The Four Files in This Folder

| File | Purpose |
|---|---|
| `scratch.md` | The complete build specification — source of truth for everything |
| `config.yaml` | OpenSpec project config — stack, architecture rules, role roster |
| `prompts.md` | 20 sequential feature prompts — one per build session |
| `WORKFLOW.md` | This file — how to use the above |

**When in doubt about any behavior, role mechanic, or architecture decision → read scratch.md first.**

---

## Before You Start Anything

### One-time setup checklist
```
[ ] Laravel project created (composer create-project laravel/laravel lwerewolf)
[ ] OpenSpec installed and configured
[ ] GitHub repo created and connected to Railway
[ ] Railway project created with 3 services (app, reverb, queue) + PostgreSQL
[ ] Railway env vars set (see scratch.md Section 33)
[ ] openspec/ folder contains: scratch.md, config.yaml, prompts.md, WORKFLOW.md
[ ] public/images/roles/ folder contains placeholder.svg + all role PNGs
[ ] Git initialized, first empty commit pushed
```

---

## The Core Rule — Never Break It

```
ONE PROMPT = ONE COMMIT.

Never start Prompt N+1 if Prompt N has:
- Failing tests
- PHP errors
- Broken migrations
- WebSocket not connecting
- Mixed content warnings in browser

Fix it first. Commit clean. Then move on.
```

This is the rule that your previous version violated.
Features built on broken ground always collapse later.

---

## The Workflow Loop (Repeat for Every Prompt)

```
┌─────────────────────────────────────────────────────┐
│  STEP 1 — Read the prompt                           │
│  STEP 2 — Generate spec with OpenSpec               │
│  STEP 3 — Review spec against scratch.md            │
│  STEP 4 — Build with OpenSpec                       │
│  STEP 5 — Test locally                              │
│  STEP 6 — Fix any issues                            │
│  STEP 7 — Commit clean                              │
│  STEP 8 — Push to Railway (from Prompt 19 onwards)  │
│  STEP 9 — Move to next prompt                       │
└─────────────────────────────────────────────────────┘
```

---

## Step-by-Step Detail

---

### STEP 1 — Read the Prompt

Open `prompts.md`. Find the prompt you are working on (e.g. Prompt 03).

Read it fully before doing anything else.
Note which sections of `scratch.md` it references — open those sections too.

```
Example: Prompt 03 references scratch.md sections 15, 16, 26.
Open scratch.md and read sections 15, 16, 26 before proceeding.
```

---

### STEP 2 — Generate the Spec with OpenSpec

Run the following command in OpenSpec:

```
/opsx:new
```

When prompted for input, say:

```
Read openspec/scratch.md and openspec/config.yaml for full project context.
Then read Prompt [XX] from openspec/prompts.md and generate a spec for it.
```

Replace [XX] with the prompt number (01, 02, 03...).

OpenSpec will generate a spec file at:
```
openspec/specs/{feature-name}.md
```

**Do not run /opsx:build yet. Read the spec first.**

---

### STEP 3 — Review the Spec Against scratch.md

This is the most important step. Do not skip it.

Open the generated spec file and check every section against `scratch.md`.

**Questions to ask while reviewing:**

```
□ Does every role behavior match scratch.md Section 21 exactly?
□ Does every event use the correct channel from scratch.md Section 13?
□ Does every action follow the resolution order in scratch.md Section 8?
□ Does any line introduce a forbidden technology? (Redis, Vue, React, Ngrok)
□ Does any code write $state->phase directly? (Only PhaseManager can do this)
□ Does any controller contain business logic? (Controllers must be thin)
□ Are any user-facing strings hardcoded in PHP or Blade? (Must use __('key'))
□ Does anything mix HTTP and HTTPS? (Fatal in production)
□ Does any sensitive data go to room.{id} instead of player.{id}?
```

**If anything is wrong:**
```
/opsx:revise
"[Describe what is wrong and what scratch.md says it should be]"
```

**Keep revising until the spec is correct before building.**

---

### STEP 4 — Build with OpenSpec

Once the spec is reviewed and correct:

```
/opsx:build
```

Or if OpenSpec uses a task system:
```
/opsx:tasks    ← generates task list
/opsx:execute  ← agent executes tasks one by one
```

Watch what the agent writes. If it goes off-spec, stop it and correct.

---

### STEP 5 — Test Locally

After the agent finishes, run your full local test suite:

```bash
# Start all services
php artisan serve --host=0.0.0.0 --port=8000
php artisan reverb:start
php artisan queue:work
npm run dev
```

Then run through this checklist for the current prompt:

```
□ No PHP errors in terminal output
□ No errors in browser console
□ No mixed content warnings (HTTP/HTTPS mismatch)
□ php artisan migrate runs without errors
□ npm run build completes without errors
□ The feature described in the prompt works as expected
□ Previous features still work (regression check)
```

**Prompt-specific test checklists are at the bottom of each prompt in prompts.md.**
Run those VERIFY steps explicitly.

---

### STEP 6 — Fix Any Issues

If something is broken:

**PHP / Laravel errors:**
```
Read the error message carefully.
Check scratch.md for the correct behavior.
Fix the specific file — do not refactor unrelated code.
Re-run php artisan serve and test again.
```

**Migration errors:**
```
php artisan migrate:rollback
Fix the migration file.
php artisan migrate
```

**WebSocket not connecting:**
```
Check browser devtools → Network tab → WS connections.
Verify REVERB_HOST, REVERB_PORT, VITE_REVERB_HOST, VITE_REVERB_PORT in .env.
Verify forceTLS matches your scheme (http local = false, https production = true).
Check php artisan reverb:start is running.
Check queue worker is running (events need queue to broadcast).
```

**Mixed content warning in browser console:**
```
This means one connection is HTTP and another is HTTPS — or WS vs WSS.
In local: everything should be HTTP + WS (not HTTPS/WSS).
In production: everything must be HTTPS + WSS.
Check .env APP_URL scheme matches REVERB_SCHEME.
Check bootstrap.js forceTLS value.
NEVER ignore this warning — it will break WebSockets silently.
```

**Feature behaves differently than spec:**
```
Re-read scratch.md section referenced in the prompt.
The spec is always right. The code is wrong.
Fix the code to match scratch.md.
```

Do not move on until all issues are resolved.

---

### STEP 7 — Commit Clean

Once all tests pass and the feature works:

```bash
git add .
git commit -m "feat: prompt [XX] — [short description]"
```

Commit message examples:
```
feat: prompt 01 — scaffold + railway config
feat: prompt 02 — migrations + models
feat: prompt 03 — authentication + session token
feat: prompt 04 — lobby service + join/create
feat: prompt 05 — narrator lobby UI
feat: prompt 06 — role assignment + game start
feat: prompt 07 — game engine + phase manager
feat: prompt 08 — action system + resolver
feat: prompt 09 — village role actions
feat: prompt 10 — werewolf role actions
feat: prompt 11 — neutral role actions
feat: prompt 12 — voting system + defense window
feat: prompt 13 — narrator dashboard
feat: prompt 14 — player game view
feat: prompt 15 — passive triggers + edge cases
feat: prompt 16 — websocket events
feat: prompt 17 — disconnection + new game
feat: prompt 18 — localization EN/FR
feat: prompt 19 — railway production deployment
feat: prompt 20 — full integration smoke test
```

---

### STEP 8 — Push to Railway (Prompt 19 onwards)

From Prompt 19 onwards, push to GitHub:

```bash
git push origin main
```

Railway auto-deploys on push. After deploy:

```
□ Railway app service: green (healthy)
□ Railway reverb service: green (healthy)
□ Railway queue service: green (healthy)
□ Visit APP_URL → welcome page loads on https://
□ Browser devtools → Network → WebSocket connects on wss://
□ No mixed content warnings in browser console
```

If any Railway service fails:
```
→ Check Railway service logs (Logs tab in Railway dashboard)
→ Most common issues: missing env var, wrong start command, migration failed
→ Fix → push again → Railway redeploys automatically
```

---

### STEP 9 — Move to Next Prompt

Only after:
```
✅ All VERIFY steps from the prompt pass
✅ No PHP errors
✅ No browser console errors
✅ No mixed content warnings
✅ Git commit made
✅ (Prompt 19+) Railway deploy healthy
```

Go back to STEP 1 with the next prompt number.

---

## High-Risk Prompts — Extra Caution Required

These prompts have the highest chance of introducing subtle bugs.
Spend extra time on STEP 3 (spec review) before building.

### Prompt 02 — Migrations & Models
```
Risk: Schema errors cascade to every subsequent prompt.
Extra check: Verify game_states.data JSON has ALL keys from scratch.md Section 4.
Extra check: Verify all foreign keys reference correct tables.
Extra check: Verify all boolean casts on Player model.
If migrations break later: php artisan migrate:fresh --seed (local only).
```

### Prompt 08 — Action Resolver
```
Risk: Resolution order errors cause wrong players to die.
Extra check: Knight Rusty Sword MUST resolve before all other actions.
Extra check: Bodyguard blocks wolf kill AND witch poison (not others).
Extra check: Wolf Father convert CANCELS wolf kill entirely that night.
Extra check: Death chain (Hunter → Lover → loop) must complete before WinConditionChecker.
Test: create a game with Knight + Wolf — verify infection resolves correctly next night.
```

### Prompt 12 — Voting & Defense Window
```
Risk: Vote phase state machine has many transitions that can get out of sync.
Extra check: Defense window opens and closes narrator-only (no auto-advance).
Extra check: Slave vote locks until master votes — enforced server-side not just UI.
Extra check: Devoted Servant swap happens BEFORE public announcement, invisibly.
Extra check: Scapegoat decree submitted BEFORE elimination fires.
Test: simulate a tie vote — verify Scapegoat flow end to end.
```

### Prompt 16 — WebSocket Events
```
Risk: Wrong channel sends sensitive data to wrong players.
Extra check: Role, night results, lover info, Kira results → player.{id} ONLY.
Extra check: Kira identity → narrator.{room_id} ONLY.
Extra check: Action feed details → narrator.{room_id} ONLY.
Extra check: No event fired directly from controller or service.
Test: open two browser tabs (player A + player B) — verify player A cannot receive player B events.
```

### Prompt 19 — Railway Production
```
Risk: One wrong env var breaks WSS and causes mixed content errors.
Extra check: APP_URL starts with https://
Extra check: REVERB_SCHEME=https
Extra check: VITE_REVERB_SCHEME=https
Extra check: REVERB_PORT=443 (external) not 8080 (internal)
Extra check: VITE_REVERB_PORT=443
Extra check: forceTLS=true in bootstrap.js when scheme is https
Extra check: NgrokHeaders middleware NOT applied in production
Test: open browser devtools → Network tab → confirm WebSocket shows wss:// not ws://
Test: browser console shows zero mixed content warnings
```

---

## Common Mistakes to Avoid

### ❌ Skipping the spec review (Step 3)
The agent will sometimes invent behavior not in scratch.md.
Always review before building. Costs 5 minutes. Saves hours of debugging.

### ❌ Moving to the next prompt with a broken feature
"I'll fix it later" always becomes "I can't find where it broke."
Fix it now. Commit clean. Always.

### ❌ Editing generated code while the agent is still running
Wait for the agent to finish. Then make corrections.
Parallel edits cause conflicts and confusion.

### ❌ Ignoring mixed content warnings
```
"Mixed Content: The page at 'https://...' was loaded over HTTPS,
but attempted to connect to the insecure WebSocket endpoint 'ws://...'"
```
This warning means WebSockets will fail silently in some browsers.
It is never safe to ignore. Fix it immediately.

### ❌ Adding features not in scratch.md
Do not add features between prompts.
If you have a new idea, add it to scratch.md first, then create a new prompt for it.
Undocumented features break the source-of-truth contract.

### ❌ Running php artisan migrate:fresh in production
This deletes all data. Only use locally.
Railway production uses `php artisan migrate --force` (safe — only runs new migrations).

### ❌ Hardcoding strings in Blade or PHP
```php
// WRONG
echo "Vous êtes amoureux";

// RIGHT
echo __('game.lover_notification');
```
All user-facing strings must go through lang/en/ and lang/fr/.

---

## Quick Reference — Key Files

```
openspec/
├── scratch.md       ← SOURCE OF TRUTH — read this when in doubt
├── config.yaml      ← OpenSpec project config
├── prompts.md       ← 20 feature prompts — one per session
└── WORKFLOW.md      ← This file

app/
├── Game/Engine/     ← GameEngine, PhaseManager (ONLY one that changes phase), ActionResolver, WinConditionChecker
├── Game/Actions/    ← 14 action classes — all extend BaseAction
├── Game/Roles/      ← 27 role classes
├── Events/          ← 21 events — all ShouldBroadcast
└── Game/Services/   ← LobbyService, ActionService, VotingService, RoleAssignmentService

resources/
├── css/app.css      ← TailwindCSS theme + components + animations
├── js/bootstrap.js  ← Echo + Pusher WebSocket setup
└── views/
    ├── layouts/app.blade.php
    └── livewire/    ← All Livewire component views

public/images/roles/ ← Role PNGs + placeholder.svg

lang/
├── en/              ← ui, roles, game, narration, lobby, decoys
└── fr/              ← mirror of en/
```

---

## Quick Reference — Architecture Rules (Never Violate)

```
1.  Controllers are thin — no business logic ever
2.  PhaseManager is the ONLY class that writes $state->phase
3.  Actions stored pending — resolved only in ActionResolver::resolve()
4.  Never broadcast directly — always fire ShouldBroadcast events
5.  Sensitive data → player.{id} only, never room.{id}
6.  WinConditionChecker runs after every elimination + enchant
7.  No hardcoded role logic in Engine — use interfaces
8.  All strings via __('key') — never hardcode FR/EN
9.  Death chains complete before WinConditionChecker
10. Narrator is never a player — no role, no vote, no night action
11. Every request verified against session_token ownership
12. No mixed content — HTTP/HTTPS must match across all connections
13. NgrokHeaders: local only, never production
14. SQLite: local only, PostgreSQL on Railway
15. Defense window: narrator-controlled, never auto-advances
16. Silence clears automatically at night transition
17. Devoted Servant swap: invisible, pre-submitted, before announcement
18. Seat order: set in lobby, locked at game start, never changes
```

---

## Quick Reference — Night Order

```
Night 1 only:
  0   Cupid
  1   Master (2 initial slaves)
  2   Silencer
  3   Wolf Hound (faction choice)

Every night:
  4   Accursed Wolf Father
  5   Werewolf pack
  6   Big Bad Wolf
  7   White Werewolf (even rounds only)
  8   Bodyguard
  9   Little Girl (passive)
  10  Seer
  11  Witch
  12  Pied Piper
  13  Fox
  14  Bear Tamer (passive)
  15  Master (+1 slave each night after night 1)
  16  Kira (always last, night 2+ only)
```

---

## Quick Reference — Win Conditions (Priority Order)

```
1. Angel        round===1 && voted out
2. White WW     last alive player, alone
3. Pied Piper   all living players enchanted
4. Kira         3 correct role guesses
5. Werewolves   wolf count >= village count
6. Village      no alive wolves
7. Lovers       last 2 alive, CoupleBond pair, different factions
```

---

## Quick Reference — Role Image System

```
File location:  public/images/roles/{role_key}.png      (400×560px)
Retina:         public/images/roles/{role_key}@2x.png   (800×1120px)
Fallback:       public/images/roles/placeholder.svg

Role keys (27 total):
  villager, seer, witch, hunter, bodyguard, little_girl, cupid,
  elder, scapegoat, village_idiot, two_sisters, three_brothers,
  stuttering_judge, knight_with_rusty_sword, devoted_servant,
  bear_tamer, fox, the_master, werewolf, big_bad_wolf,
  accursed_wolf_father, white_werewolf, wolf_hound, silencer,
  angel, pied_piper, kira

To add an image: drop the PNG in public/images/roles/ → push → done.
No code changes needed.
```

---

## When You're Stuck

```
Something behaves wrong?
→ Read scratch.md section referenced in the prompt.
→ The spec is always right. Fix the code.

Agent invented behavior not in spec?
→ /opsx:revise "This behavior is not in scratch.md. Section X says: [exact quote]"

WebSocket not connecting locally?
→ Check: php artisan reverb:start is running
→ Check: php artisan queue:work is running
→ Check: .env REVERB_HOST=localhost, REVERB_PORT=8080, REVERB_SCHEME=http
→ Check: bootstrap.js forceTLS=false for local

WebSocket not connecting on Railway?
→ Check: REVERB_HOST=your-reverb-service.up.railway.app (no https://)
→ Check: REVERB_PORT=443
→ Check: REVERB_SCHEME=https
→ Check: VITE_REVERB_PORT=443
→ Check: VITE_REVERB_SCHEME=https
→ Check: Railway reverb service logs — is it running?
→ Browser devtools Network tab — is it connecting to wss:// not ws://?

Migration conflict?
→ Local: php artisan migrate:fresh --seed (wipes local DB, reseeds)
→ Production: NEVER use migrate:fresh — use migrate --force only

Stuck for more than 30 minutes on one issue?
→ git stash
→ Re-read the relevant scratch.md section
→ Start the prompt over with /opsx:new
→ Sometimes a fresh spec generation resolves it
```

---

## Progress Tracker

Use this to track your progress. Check off each prompt as it's committed.

```
[ ] Prompt 01 — Project Scaffold & Railway Config
[ ] Prompt 02 — Database Migrations & Models
[ ] Prompt 03 — Authentication & AppServiceProvider
[ ] Prompt 04 — Lobby: CreateRoom, JoinRoom, LobbyService
[ ] Prompt 05 — Narrator Lobby (NarratorLobby Livewire)
[ ] Prompt 06 — Role Assignment & Game Start
[ ] Prompt 07 — Game Engine & Phase Manager
[ ] Prompt 08 — Action System (Interface, BaseAction, ActionResolver)
[ ] Prompt 09 — Village Role Actions
[ ] Prompt 10 — Werewolf Role Actions
[ ] Prompt 11 — Neutral Role Actions
[ ] Prompt 12 — Voting System & Defense Window
[ ] Prompt 13 — Narrator Dashboard (NarratorDashboard Livewire)
[ ] Prompt 14 — Player Game View (PlayerGameView Livewire)
[ ] Prompt 15 — Passive Role Triggers & Edge Cases
[ ] Prompt 16 — WebSocket Events & Real-time Layer
[ ] Prompt 17 — Disconnection, Kick & New Game
[ ] Prompt 18 — Localization (EN/FR)
[ ] Prompt 19 — Railway Production Deployment
[ ] Prompt 20 — Final Integration & Smoke Test
```

---

*Good luck. One prompt at a time. Commit clean. Never build on broken ground.*
