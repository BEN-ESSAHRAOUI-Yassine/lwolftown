## Why

The app needs cookie-based player authentication and a configured AppServiceProvider to function. Without this, no route can identify which player is making a request, channel auth won't work, and the session-token guard doesn't exist. This is foundational infrastructure that must be in place before any lobby or game features.

## What Changes

- Update AppServiceProvider boot(): locale from session, register session-token auth guard via `auth()->viaRequest()`
- Update config/auth.php: add `session-token` guard with custom driver
- Verify IdentifyPlayer middleware correctly reads cookie and merges player onto request
- Verify WebSocket channel auth in routes/channels.php for all 4 channel types
- Verify broadcasting auth endpoint works

## Capabilities

### New Capabilities
- `cookie-authentication`: Cookie-based player identity using session_token UUID, AppServiceProvider guard registration, IdentifyPlayer middleware behavior, channel authorization rules

### Modified Capabilities
- `middleware-setup`: AppServiceProvider boot() updates (locale, DB destructive commands prohibition) — delta spec needed

## Impact

- `app/Providers/AppServiceProvider.php` — boot() method changes
- `config/auth.php` — new guard definition
- `app/Http/Middleware/IdentifyPlayer.php` — verify behavior (already exists from Prompt 01)
- `routes/channels.php` — channel auth rules (already exists from Prompt 01)
- No new dependencies
