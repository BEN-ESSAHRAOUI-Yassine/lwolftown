## Why

The lwolftown project requires a clean Laravel 13.7 scaffold with the full tech stack configured before any game logic can be built. This is the foundational change — without it, no other prompts (migrations, models, services, UI) can proceed. The app must also be deployment-ready for Railway from day one (3 services: app, reverb, queue).

## What Changes

- Fresh Laravel 13.7 / PHP 8.3 project scaffolded with all dependencies installed
- TailwindCSS v4 configured via CSS-first config (no tailwind.config.js) with full dark theme, animations, and atmospheric components
- Laravel Reverb configured for WebSocket broadcasting with ESM-compatible bootstrap.js
- Livewire v4.1 assets injected
- QR code generation library (chillerlan/php-qrcode) integrated
- Vite configured with @tailwindcss/vite plugin
- Base Blade layout with Cinzel + Inter typography, fog/vignette atmosphere
- Railway deployment config files (railway.app.json, railway.reverb.json, railway.queue.json)
- Environment templates: .env (local SQLite) and .env.example (production PostgreSQL)
- IdentifyPlayer middleware appended to web group
- NgrokHeaders middleware prepended in local environment only
- bootstrap/app.php: CSRF exceptions, trustProxies, middleware wiring
- Role image directory scaffolded with placeholder.svg (atmospheric silhouette)
- AppServiceProvider: session-token auth guard, locale from session, CarbonImmutable

## Capabilities

### New Capabilities

- `project-scaffold`: Laravel project setup, all Composer/npm dependencies, Vite config, CSS theme, Blade layout, bootstrap.js WebSocket setup, .env templates, package.json ESM config
- `railway-deployment`: Railway JSON config files for 3 services (app, reverb, queue), production env vars, Caddyfile, HTTPS/WSS enforcement, no mixed content
- `middleware-setup`: IdentifyPlayer middleware (session-token cookie → Player lookup), NgrokHeaders middleware (local only), bootstrap/app.php wiring
- `role-images`: public/images/roles/ directory with placeholder.svg fallback, Blade srcset helper logic for future role PNGs

### Modified Capabilities

<!-- No existing specs to modify — this is the first change. -->

## Impact

- **Files created**: vite.config.js, resources/css/app.css, resources/js/bootstrap.js, resources/views/layouts/app.blade.php, railway.app.json, railway.reverb.json, railway.queue.json, .env, .env.example, app/Http/Middleware/IdentifyPlayer.php, app/Http/Middleware/NgrokHeaders.php, app/Providers/AppServiceProvider.php, public/images/roles/placeholder.svg, config/auth.php (guard addition), config/reverb.php, config/livewire.php
- **Dependencies added**: livewire/livewire, laravel/reverb, chillerlan/php-qrcode, tailwindcss, @tailwindcss/vite, laravel-echo, pusher-js, axios
- **Systems affected**: All future code depends on this scaffold — every model, service, Livewire component, and event will use these foundations
- **Breaking**: None — this is the initial setup
