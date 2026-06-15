## ADDED Requirements

### Requirement: Laravel 13.7 project with all dependencies
The system SHALL be a fresh Laravel 13.7 project with PHP 8.3, with all specified Composer and npm dependencies installed and verified.

#### Scenario: Composer dependencies installed
- **WHEN** `composer require livewire/livewire laravel/reverb chillerlan/php-qrcode` is executed
- **THEN** packages are installed without errors and appear in composer.json

#### Scenario: npm dependencies installed
- **WHEN** `npm install tailwindcss @tailwindcss/vite laravel-echo pusher-js axios` is executed
- **THEN** packages are installed without errors and appear in package.json with `"type": "module"`

#### Scenario: Build succeeds
- **WHEN** `npm run build` is executed
- **THEN** Vite compiles successfully with no errors

#### Scenario: Artisan serve starts
- **WHEN** `php artisan serve` is executed
- **THEN** the development server starts without errors

### Requirement: Vite configuration with TailwindCSS v4
The system SHALL use vite.config.js with laravel-vite-plugin and @tailwindcss/vite plugin. The configuration SHALL use ESM syntax.

#### Scenario: Vite config references correct inputs
- **WHEN** vite.config.js is loaded
- **THEN** it references `resources/css/app.css` and `resources/js/app.js` as inputs

#### Scenario: Vite config uses TailwindCSS plugin
- **WHEN** vite.config.js is loaded
- **THEN** the tailwindcss plugin from `@tailwindcss/vite` is included in the plugins array

#### Scenario: No tailwind.config.js exists
- **WHEN** the project root is inspected
- **THEN** no tailwind.config.js file exists

### Requirement: CSS theme with dark atmospheric design
The system SHALL include resources/css/app.css with TailwindCSS v4 CSS-first configuration, a full `@theme` block, component classes, and keyframe animations as specified in scratch.md Section 32.

#### Scenario: Theme defines all design tokens
- **WHEN** app.css is compiled
- **THEN** it defines --font-sans (Inter), --font-serif (Cinzel), and all color tokens (bg-primary, bg-surface, bg-elevated, text-primary, text-secondary, accent-warm, accent-danger, accent-village, accent-neutral, accent-lovers, masked-card, dead-player)

#### Scenario: Phase overlay components exist
- **WHEN** app.css is compiled
- **THEN** classes .phase-overlay, .phase-overlay-night, .phase-overlay-day, .phase-overlay-voting, .phase-overlay-finished are defined

#### Scenario: Card components exist
- **WHEN** app.css is compiled
- **THEN** classes .card-masked and .card-revealed are defined

#### Scenario: Atmospheric components exist
- **WHEN** app.css is compiled
- **THEN** classes .fog-layer and .vignette are defined

#### Scenario: Animations are defined
- **WHEN** app.css is compiled
- **THEN** keyframes phaseOverlayIn, fogDrift, candleFlicker, pulseGlow, elementFadeIn, bellToll exist

#### Scenario: Alpine.js cloak rule exists
- **WHEN** app.css is compiled
- **THEN** the rule `[x-cloak] { display: none !important; }` is present

### Requirement: Bootstrap.js WebSocket setup
The system SHALL include resources/js/bootstrap.js with Laravel Echo configured for Reverb, using environment variables for all connection parameters.

#### Scenario: Echo configured with Reverb broadcaster
- **WHEN** bootstrap.js is loaded
- **THEN** window.Echo is instantiated with `broadcaster: 'reverb'`

#### Scenario: Connection params from env vars
- **WHEN** bootstrap.js reads configuration
- **THEN** key, wsHost, wsPort, wssPort, and forceTLS all read from import.meta.env.VITE_REVERB_* variables

#### Scenario: forceTLS derived from scheme
- **WHEN** VITE_REVERB_SCHEME is 'https'
- **THEN** forceTLS is true
- **WHEN** VITE_REVERB_SCHEME is 'http'
- **THEN** forceTLS is false

#### Scenario: Enabled transports include ws and wss
- **WHEN** bootstrap.js configures Echo
- **THEN** enabledTransports is ['ws', 'wss']

### Requirement: Base Blade layout
The system SHALL include resources/views/layouts/app.blade.php with dark theme, Cinzel/Inter fonts, CSRF meta tag, Vite assets, Livewire styles/scripts, fog layer, and vignette.

#### Scenario: Layout includes font imports
- **WHEN** the layout renders
- **THEN** Google Fonts links for Cinzel (400,600,700) and Inter (300,400,500,600) are in the head

#### Scenario: Layout includes Vite assets
- **WHEN** the layout renders
- **THEN** `@vite(['resources/css/app.css', 'resources/js/app.js'])` is present

#### Scenario: Layout includes Livewire assets
- **WHEN** the layout renders
- **THEN** `@livewireStyles` is in the head and `@livewireScripts` is before closing body

#### Scenario: Layout has dark theme classes
- **WHEN** the layout renders
- **THEN** the body has classes `bg-[#0D0D0D] text-[#E8D9B5] antialiased min-h-screen font-sans`

#### Scenario: Layout has atmospheric elements
- **WHEN** the layout renders
- **THEN** div.fog-layer and div.vignette are present

#### Scenario: Layout has CSRF meta tag
- **WHEN** the layout renders
- **THEN** a meta tag with name="csrf-token" and content from csrf_token() is present

### Requirement: AppServiceProvider configuration
The system SHALL configure AppServiceProvider boot() to set CarbonImmutable, prohibit destructive DB commands in production, load locale from session, and register session-token auth guard.

#### Scenario: CarbonImmutable set
- **WHEN** AppServiceProvider boots
- **THEN** Date::use(CarbonImmutable::class) is called

#### Scenario: Destructive commands prohibited in production
- **WHEN** app is in production
- **THEN** DB::prohibitDestructiveCommands returns true

#### Scenario: Locale loaded from session
- **WHEN** session contains 'locale' key with value 'en' or 'fr'
- **THEN** app locale is set to that value

#### Scenario: Session-token auth guard registered
- **WHEN** auth guard 'session-token' is resolved
- **THEN** it reads the 'session_token' cookie and returns matching Player or null

### Requirement: Config files
The system SHALL include config/auth.php with session-token guard, config/reverb.php with Reverb server settings, and config/livewire.php with specified payload limits.

#### Scenario: Auth config has session-token guard
- **WHEN** config/auth.php is loaded
- **THEN** guards array contains 'session-token' with driver 'session-token'

#### Scenario: Reverb config reads from env
- **WHEN** config/reverb.php is loaded
- **THEN** host, port, and scheme read from REVERB_HOST, REVERB_PORT, REVERB_SCHEME env vars

#### Scenario: Reverb config sets useTLS from scheme
- **WHEN** REVERB_SCHEME is 'https'
- **THEN** useTLS is true

#### Scenario: Livewire config has correct limits
- **WHEN** config/livewire.php is loaded
- **THEN** payload max_size is 1048576, max_nesting_depth is 10, max_calls is 50, max_components is 200

### Requirement: Environment templates
The system SHALL include .env for local development (SQLite, HTTP, localhost) and .env.example as production template (PostgreSQL, HTTPS, Railway domains).

#### Scenario: Local .env uses SQLite
- **WHEN** .env is loaded
- **THEN** DB_CONNECTION is sqlite

#### Scenario: Local .env uses HTTP scheme
- **WHEN** .env is loaded
- **THEN** REVERB_SCHEME is http and VITE_REVERB_SCHEME is http

#### Scenario: .env.example uses PostgreSQL
- **WHEN** .env.example is loaded
- **THEN** DB_CONNECTION is pgsql with DATABASE_URL placeholder

#### Scenario: .env.example uses HTTPS scheme
- **WHEN** .env.example is loaded
- **THEN** REVERB_SCHEME is https and VITE_REVERB_SCHEME is https

#### Scenario: APP_URL matches scheme
- **WHEN** .env is loaded
- **THEN** APP_URL scheme matches REVERB_SCHEME (no mixed content)

### Requirement: package.json ESM configuration
The system SHALL include package.json with `"type": "module"`.

#### Scenario: ESM module type set
- **WHEN** package.json is parsed
- **THEN** `"type"` is `"module"`
