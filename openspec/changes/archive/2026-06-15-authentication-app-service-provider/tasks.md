## 1. AppServiceProvider Configuration

- [x] 1.1 Update AppServiceProvider boot(): add DB::prohibitDestructiveCommands() wrapped in app()->environment('local') check
- [x] 1.2 Update AppServiceProvider boot(): read session('locale'), set app locale if 'en' or 'fr', default to 'en'
- [x] 1.3 Update AppServiceProvider boot(): register 'session-token' auth guard via auth()->viaRequest() that reads session_token cookie and returns Player::where('session_token', $token)->first() or null

## 2. Auth Config

- [x] 2.1 Verify config/auth.php has 'session-token' guard with driver 'session-token' and no provider

## 3. Verification

- [x] 3.1 Test: auth guard resolves Player from valid session_token cookie (RequestGuard confirmed)
- [x] 3.2 Test: auth guard returns null for missing/invalid cookie (closure returns null when no cookie)
- [x] 3.3 Test: locale set from session('locale') when 'en' or 'fr'
- [x] 3.4 Test: locale defaults to 'en' when session key absent
- [x] 3.5 Verify /broadcasting/auth endpoint accepts POST with valid cookie (CSRF exempt confirmed)
- [x] 3.6 Verify channel auth works for all 4 channel types (player, narrator, werewolves, room confirmed)
