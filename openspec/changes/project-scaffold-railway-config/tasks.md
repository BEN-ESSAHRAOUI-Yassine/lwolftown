## 1. Project Scaffold & Dependencies

- [x] 1.1 Create fresh Laravel 13.7 project with PHP 8.3
- [x] 1.2 Install Composer dependencies: livewire/livewire, laravel/reverb, chillerlan/php-qrcode
- [x] 1.3 Install npm dependencies: tailwindcss, @tailwindcss/vite, laravel-echo, pusher-js, axios
- [x] 1.4 Set `"type": "module"` in package.json
- [x] 1.5 Verify `npm run build` succeeds
- [x] 1.6 Verify `php artisan serve` starts

## 2. Vite Configuration

- [x] 2.1 Create vite.config.js with laravel-vite-plugin and @tailwindcss/vite plugin (scratch.md Section 30)
- [x] 2.2 Verify no tailwind.config.js exists

## 3. CSS Theme & Assets

- [x] 3.1 Create resources/css/app.css with full @theme block (all color tokens, font families) per scratch.md Section 32
- [x] 3.2 Add component classes: .phase-overlay variants, .card-masked, .card-revealed, .fog-layer, .vignette
- [x] 3.3 Add keyframe animations: phaseOverlayIn, fogDrift, candleFlicker, pulseGlow, elementFadeIn, bellToll
- [x] 3.4 Add [x-cloak] rule for Alpine.js

## 4. JavaScript Bootstrap

- [x] 4.1 Create resources/js/bootstrap.js with axios setup, Echo/Reverb configuration per scratch.md Section 28
- [x] 4.2 Verify forceTLS reads from VITE_REVERB_SCHEME env var
- [x] 4.3 Verify wsPort and wssPort both read from VITE_REVERB_PORT

## 5. Blade Layout

- [x] 5.1 Create resources/views/layouts/app.blade.php per scratch.md Section 31
- [x] 5.2 Include Google Fonts (Cinzel + Inter) preconnect links
- [x] 5.3 Include @vite, @livewireStyles, @livewireScripts
- [x] 5.4 Include fog-layer and vignette atmospheric divs
- [x] 5.5 Include CSRF meta tag

## 6. Configuration Files

- [x] 6.1 Update config/auth.php: add 'session-token' guard with driver 'session-token'
- [x] 6.2 Update config/reverb.php: Reverb server settings reading from env vars, useTLS derived from REVERB_SCHEME, allowed_origins ['*']
- [x] 6.3 Update config/livewire.php: inject_assets=true, navigate progress bar #C8922A, payload limits (max_size 1048576, max_nesting_depth 10, max_calls 50, max_components 200)

## 7. Environment Templates

- [x] 7.1 Create .env for local development: sqlite, localhost reverb, http scheme, APP_URL=http://localhost:8000
- [x] 7.2 Create .env.example as production template: pgsql, Railway domains, https scheme, all critical vars present
- [x] 7.3 Verify APP_URL, REVERB_SCHEME, VITE_REVERB_SCHEME are consistent (no mixed content possible)

## 8. Middleware

- [x] 8.1 Create app/Http/Middleware/IdentifyPlayer.php: reads session_token cookie, finds Player, merges _player, does NOT abort if missing
- [x] 8.2 Create app/Http/Middleware/NgrokHeaders.php: trusts Ngrok proxy headers
- [x] 8.3 Update bootstrap/app.php: append IdentifyPlayer to web group, CSRF except /broadcasting/auth, trustProxies at *
- [x] 8.4 Update bootstrap/app.php: prepend NgrokHeaders only when env('APP_ENV') === 'local'

## 9. AppServiceProvider

- [x] 9.1 Update app/Providers/AppServiceProvider.php boot(): Date::use(CarbonImmutable), DB::prohibitDestructiveCommands in production, locale from session
- [x] 9.2 Register 'session-token' auth guard via auth()->viaRequest() — reads cookie, returns Player or null

## 10. WebSocket Channels

- [x] 10.1 Create routes/channels.php with player.{playerId}, narrator.{roomId}, werewolves.{roomId}, room.{roomId} authorization rules per scratch.md Section 14

## 11. Role Image Directory

- [x] 11.1 Create public/images/roles/ directory (already existed with 27 role images + @2x variants)
- [x] 11.2 Create public/images/roles/placeholder.svg: ViewBox 0 0 400 560, #C8922A stroke, no fill, humanoid silhouette, transparent background

## 12. Railway Deployment Config

- [x] 12.1 Create railway.app.json: Railpack builder, FrankenPHP start command, health check at /, restart on failure
- [x] 12.2 Create railway.reverb.json: Railpack builder, reverb:start --host=0.0.0.0 --port=8080, restart always
- [x] 12.3 Create railway.queue.json: Railpack builder, queue:work --tries=3 --timeout=60, restart always

## 13. Verification

- [x] 13.1 Run `npm run build` — no errors
- [x] 13.2 Run `php artisan serve` — starts successfully (Status 200)
- [x] 13.3 Verify public/images/roles/placeholder.svg exists and renders correctly
- [x] 13.4 Verify no mixed content warnings possible from this config (APP_URL scheme matches REVERB_SCHEME)
- [x] 13.5 Verify IdentifyPlayer middleware appended to web group
- [x] 13.6 Verify NgrokHeaders only active in local environment
