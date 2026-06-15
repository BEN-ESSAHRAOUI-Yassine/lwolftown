## ADDED Requirements

### Requirement: Railway app service configuration
The system SHALL include railway.app.json with FrankenPHP start command, health check, and Railpack builder.

#### Scenario: App service config exists
- **WHEN** railway.app.json is parsed
- **THEN** deploy.startCommand is `php artisan migrate --force && php artisan optimize && frankenphp run --config /etc/caddy/Caddyfile`

#### Scenario: App service has health check
- **WHEN** railway.app.json is parsed
- **THEN** deploy.healthcheckPath is `/` and healthcheckTimeout is 30

#### Scenario: App service uses Railpack builder
- **WHEN** railway.app.json is parsed
- **THEN** build.builder is `RAILPACK`

### Requirement: Railway Reverb service configuration
The system SHALL include railway.reverb.json with Reverb start command and always-restart policy.

#### Scenario: Reverb service config exists
- **WHEN** railway.reverb.json is parsed
- **THEN** deploy.startCommand is `php artisan reverb:start --host=0.0.0.0 --port=8080`

#### Scenario: Reverb service always restarts
- **WHEN** railway.reverb.json is parsed
- **THEN** deploy.restartPolicyType is `ALWAYS`

### Requirement: Railway Queue service configuration
The system SHALL include railway.queue.json with queue worker start command and always-restart policy.

#### Scenario: Queue service config exists
- **WHEN** railway.queue.json is parsed
- **THEN** deploy.startCommand is `php artisan queue:work --tries=3 --timeout=60`

#### Scenario: Queue service always restarts
- **WHEN** railway.queue.json is parsed
- **THEN** deploy.restartPolicyType is `ALWAYS`

### Requirement: No mixed content in production
The system SHALL ensure all connections use HTTPS/WSS. APP_URL, REVERB_SCHEME, and VITE_REVERB_SCHEME must all use the same scheme.

#### Scenario: APP_URL uses HTTPS
- **WHEN** .env.example is loaded
- **THEN** APP_URL starts with `https://`

#### Scenario: REVERB_SCHEME matches APP_URL scheme
- **WHEN** .env.example is loaded
- **THEN** REVERB_SCHEME is `https`

#### Scenario: VITE_REVERB_SCHEME matches
- **WHEN** .env.example is loaded
- **THEN** VITE_REVERB_SCHEME is `https`

#### Scenario: No HTTP assets on HTTPS page
- **WHEN** the app is loaded in production
- **THEN** no asset URL uses `http://` scheme

### Requirement: Railway environment variables template
The system SHALL include all required production environment variables in .env.example with correct Railway domain placeholders.

#### Scenario: All critical vars present
- **WHEN** .env.example is parsed
- **THEN** it contains APP_NAME, APP_ENV, APP_KEY, APP_DEBUG, APP_URL, DB_CONNECTION, DATABASE_URL, BROADCAST_CONNECTION, QUEUE_CONNECTION, SESSION_DRIVER, CACHE_STORE, LOG_CHANNEL, REVERB_APP_ID, REVERB_APP_KEY, REVERB_APP_SECRET, REVERB_HOST, REVERB_PORT, REVERB_SCHEME, VITE_REVERB_APP_KEY, VITE_REVERB_HOST, VITE_REVERB_PORT, VITE_REVERB_SCHEME

#### Scenario: SESSION_DRIVER is database
- **WHEN** .env.example is loaded
- **THEN** SESSION_DRIVER is `database`

#### Scenario: CACHE_STORE is database
- **WHEN** .env.example is loaded
- **THEN** CACHE_STORE is `database`

#### Scenario: QUEUE_CONNECTION is database
- **WHEN** .env.example is loaded
- **THEN** QUEUE_CONNECTION is `database`

### Requirement: Local development configuration
The system SHALL support local development with SQLite, HTTP scheme, and localhost Reverb.

#### Scenario: Local .env uses SQLite
- **WHEN** .env is loaded
- **THEN** DB_CONNECTION is `sqlite`

#### Scenario: Local .env uses localhost Reverb
- **WHEN** .env is loaded
- **THEN** REVERB_HOST is `localhost` and REVERB_PORT is `8080`

#### Scenario: Local .env uses HTTP scheme
- **WHEN** .env is loaded
- **THEN** REVERB_SCHEME is `http` and VITE_REVERB_SCHEME is `http`

#### Scenario: Local .env uses localhost APP_URL
- **WHEN** .env is loaded
- **THEN** APP_URL is `http://localhost:8000`
