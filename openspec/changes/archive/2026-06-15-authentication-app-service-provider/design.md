## Context

The Loup-Garou companion app uses a cookie-based session_token UUID for player identity instead of traditional user accounts. Prompt 01 created the IdentifyPlayer middleware and channel auth rules, but the AppServiceProvider has placeholder implementations that need to be completed. The auth guard registration, locale handling, and database protection need to be finalized.

Current state:
- `app/Providers/AppServiceProvider.php` has partial boot() with CarbonImmutable and locale, but auth guard not properly registered
- `config/auth.php` has session-token guard stub
- `app/Http/Middleware/IdentifyPlayer.php` exists and works
- `routes/channels.php` exists with all 4 channel auth rules

## Goals / Non-Goals

**Goals:**
- Register `session-token` auth guard via `auth()->viaRequest()` that resolves Player from cookie
- Set locale from session ('en' or 'fr')
- Prohibit destructive DB commands in production
- Verify channel auth works for all 4 channel types
- Verify broadcasting auth endpoint works

**Non-Goals:**
- No changes to IdentifyPlayer middleware (already correct)
- No changes to channel auth rules (already correct)
- No user registration/login flows (no user accounts)
- No password handling

## Decisions

### Auth guard registration approach
Use `auth()->viaRequest()` in AppServiceProvider boot() to register a custom guard that:
1. Reads the `session_token` cookie from the request
2. Queries `Player::where('session_token', $token)->first()`
3. Returns the Player instance (or null)

This is simpler than creating a custom UserProvider — the Player model already acts as the user entity.

### Locale handling
Read `session('locale')` in boot(). If it's 'en' or 'fr', call `app()->setLocale()`. Otherwise default to 'en'. Locale is set during room creation via LobbyService.

### DB prohibitDestructiveCommands
Use `DB::prohibitDestructiveCommands()` in production to prevent accidental `migrate:fresh` or `db:wipe` on the production database.

## Risks / Trade-offs

- **[Risk]** Cookie not sent on first request → IdentifyPlayer returns null, no abort → Player simply isn't authenticated. This is by design — unauthenticated users can access welcome page.
- **[Risk]** Session_token collision → UUID v4 collision is astronomically unlikely. No mitigation needed.
- **[Trade-off]** Using `viaRequest` closure vs custom UserProvider → closure is simpler but doesn't support `remember()` or `viaRemember()`. Acceptable since we don't need persistent sessions.
