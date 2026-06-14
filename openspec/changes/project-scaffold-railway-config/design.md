## Context

This is the first change for lwolftown — a real-life social deduction companion app. There is no existing codebase; we are scaffolding from scratch. The project must support Laravel 13.7, PHP 8.3, Livewire 4.1, TailwindCSS v4 (CSS-first), Laravel Reverb for WebSockets, QR code generation, and deployment to Railway (3 services). All 19 subsequent prompts depend on this scaffold being correct.

## Goals / Non-Goals

**Goals:**
- Fresh Laravel 13.7 project with all dependencies installed and verified
- TailwindCSS v4 with CSS-first config (no tailwind.config.js ever) — full dark theme, animations, atmospheric components
- Laravel Reverb WebSocket broadcasting with ESM-compatible bootstrap.js
- Base Blade layout with Cinzel/Inter typography and fog/vignette atmosphere
- Railway deployment config (3 services: app, reverb, queue) with HTTPS/WSS enforcement
- Local development environment (SQLite, HTTP, localhost Reverb)
- IdentifyPlayer + NgrokHeaders middleware wired correctly
- Role image directory with placeholder.svg for all 27 roles
- AppServiceProvider with session-token auth guard

**Non-Game:**
- No game logic, models, migrations, or seeders (Prompt 02)
- No authentication beyond session-token cookie (Prompt 03)
- No Livewire components beyond what's needed for scaffold verification
- No localization files (Prompt 18)
- No role images (only placeholder.svg)

## Decisions

### D1: TailwindCSS v4 CSS-first config
**Choice**: Use `@import 'tailwindcss'` and `@theme` block in app.css. No tailwind.config.js.
**Why**: scratch.md Section 32 specifies CSS-first config. Tailwind v4 dropped JS config in favor of CSS-native theming. This is the only supported approach.
**Alternative considered**: None — tailwind.config.js is explicitly forbidden in config.yaml.

### D2: Vite plugin for Tailwind
**Choice**: Use `@tailwindcss/vite` plugin in vite.config.js.
**Why**: scratch.md Section 30 specifies this. Tailwind v4's Vite plugin handles CSS processing natively — no PostCSS config needed.
**Alternative considered**: PostCSS plugin (`@tailwindcss/postcss`) — works but Vite plugin is simpler and specified in the source.

### D3: ESM module type
**Choice**: Set `"type": "module"` in package.json.
**Why**: config.yaml specifies ESM. Vite 8+ and @tailwindcss/vite expect ESM.
**Alternative considered**: CommonJS — not compatible with Vite 8.

### D4: Railway 3-service architecture
**Choice**: Separate app (web), reverb (WebSocket), and queue (worker) services.
**Why**: scratch.md Section 33 specifies this layout. Reverb needs its own process for persistent WebSocket connections. Queue worker needs separate process for job processing.
**Alternative considered**: Single-service deployment — not possible since Reverb runs a persistent WebSocket server and queue worker runs a loop.

### D5: FrankenPHP for app service
**Choice**: Use Caddyfile with `frankenphp run` as the app start command.
**Why**: scratch.md Section 33 specifies FrankenPHP. It's the PHP-native app server integrated with Caddy, ideal for Railway.
**Alternative considered**: php-fpm + nginx — more complex setup, not specified.

### D6: NgrokHeaders in local only
**Choice**: Prepend NgrokHeaders middleware only when `app()->environment('local')`.
**Why**: scratch.md Sections 15 and 34 specify this. Ngrok headers are needed for local development with tunnel services but must never be in production.
**Alternative considered**: Conditional compilation — environment check is cleaner.

### D7: Placeholder SVG dimensions
**Choice**: ViewBox 0 0 400 560, accent-warm #C8922A stroke, no fill, humanoid silhouette.
**Why**: scratch.md Section 22 specifies exact dimensions matching role card ratio (5:7). Stroke-only on transparent background for atmospheric dark theme.
**Alternative considered**: None — dimensions and style are specified.

## Risks / Trade-offs

- **[Risk] Railway TLS termination** → Railway handles HTTPS externally, Reverb runs plain WS on port 8080 internally. Mitigation: REVERB_PORT=443 in env (external), REVERB_SERVER_PORT=8080 (internal). Never mix schemes.
- **[Risk] Tailwind v4 CSS-first is relatively new** → Fewer community examples than v3. Mitigation: The source of truth (scratch.md) provides exact CSS. Follow it precisely.
- **[Risk] ESM + Vite 8 compatibility** → Some older packages may not support ESM. Mitigation: All specified packages (laravel-echo, pusher-js, axios) support ESM.
- **[Trade-off] No Redis** → Using database for sessions, cache, and queue. Acceptable for a companion app with limited concurrent users. Config.yaml explicitly forbids Redis.
- **[Trade-off] Placeholder SVG is generic** → All 27 roles look identical until PNGs are added. Acceptable — images are a later addition.
