## ADDED Requirements

### Requirement: session-token auth guard
The system SHALL register a `session-token` auth guard via `auth()->viaRequest()` in AppServiceProvider boot() that resolves a Player model from the session_token cookie.

#### Scenario: Valid cookie resolves player
- **WHEN** a request contains a valid session_token cookie matching a Player record
- **THEN** auth()->guard('session-token')->user() returns that Player instance

#### Scenario: Missing cookie returns null
- **WHEN** a request has no session_token cookie
- **THEN** auth()->guard('session-token')->user() returns null

#### Scenario: Invalid cookie returns null
- **WHEN** a request has a session_token cookie that matches no Player record
- **THEN** auth()->guard('session-token')->user() returns null

#### Scenario: Auth guard defined in config
- **WHEN** config/auth.php is loaded
- **THEN** guards array contains 'session-token' with driver 'session-token'

### Requirement: Locale from session
The system SHALL read the 'locale' key from the session in AppServiceProvider boot() and set the application locale if the value is 'en' or 'fr'.

#### Scenario: Valid locale set from session
- **WHEN** session contains locale='fr'
- **THEN** app()->locale is 'fr'

#### Scenario: Invalid locale defaults to en
- **WHEN** session contains locale='de' or locale is absent
- **THEN** app()->locale is 'en'

### Requirement: Prohibit destructive commands in production
The system SHALL call DB::prohibitDestructiveCommands() in production environment to prevent accidental database resets.

#### Scenario: Destructive commands blocked in production
- **WHEN** app environment is 'production'
- **THEN** migrate:fresh and db:wipe commands throw an exception

#### Scenario: Destructive commands allowed in non-production
- **WHEN** app environment is 'local'
- **THEN** migrate:fresh and db:wipe commands execute normally

### Requirement: Broadcasting auth endpoint
The system SHALL expose /broadcasting/auth for WebSocket channel authentication. The endpoint SHALL be exempt from CSRF verification.

#### Scenario: Broadcasting auth accepts valid channel subscription
- **WHEN** a POST request is made to /broadcasting/auth with a valid session_token cookie and valid channel name
- **THEN** the response returns 200 with the channel authorization data

#### Scenario: Broadcasting auth rejects unauthorized subscription
- **WHEN** a POST request is made to /broadcasting/auth with no session_token cookie
- **THEN** the response returns 403
