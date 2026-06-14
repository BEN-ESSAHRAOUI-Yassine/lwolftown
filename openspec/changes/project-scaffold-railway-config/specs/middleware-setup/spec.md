## ADDED Requirements

### Requirement: IdentifyPlayer middleware
The system SHALL include IdentifyPlayer middleware that reads the session_token cookie, finds the matching Player record, and merges _player onto the request without aborting if missing.

#### Scenario: Valid session token resolves player
- **WHEN** a request has a valid session_token cookie
- **THEN** the middleware finds the Player record and merges it as _player on the request

#### Scenario: Missing session token does not abort
- **WHEN** a request has no session_token cookie
- **THEN** the middleware proceeds without aborting (no 401/403)

#### Scenario: Invalid session token does not abort
- **WHEN** a request has a session_token cookie that matches no Player
- **THEN** the middleware proceeds without aborting

#### Scenario: Middleware appended to web group
- **WHEN** bootstrap/app.php is loaded
- **THEN** IdentifyPlayer is appended to the web middleware group

### Requirement: NgrokHeaders middleware
The system SHALL include NgrokHeaders middleware that trusts Ngrok proxy headers. This middleware SHALL only be active in the local environment.

#### Scenario: NgrokHeaders active in local
- **WHEN** app environment is 'local'
- **THEN** NgrokHeaders middleware is prepended to the middleware stack

#### Scenario: NgrokHeaders not active in production
- **WHEN** app environment is 'production'
- **THEN** NgrokHeaders middleware is NOT in the middleware stack

#### Scenario: NgrokHeaders conditional in bootstrap/app.php
- **WHEN** bootstrap/app.php is loaded
- **THEN** the code checks `app()->environment('local')` before prepending NgrokHeaders

### Requirement: CSRF exception for broadcasting
The system SHALL exempt /broadcasting/auth from CSRF verification.

#### Scenario: Broadcasting auth exempt from CSRF
- **WHEN** a POST request is made to /broadcasting/auth
- **THEN** CSRF verification is not performed

#### Scenario: Other routes protected by CSRF
- **WHEN** a POST request is made to any route except /broadcasting/auth
- **THEN** CSRF verification is performed

### Requirement: Trust proxies configuration
The system SHALL configure trusted proxies to forward headers from all proxies.

#### Scenario: All proxies trusted
- **WHEN** bootstrap/app.php is loaded
- **THEN** trustProxies is set to at '*' with headers X_FORWARDED_FOR|HOST|PORT|PROTO

### Requirement: WebSocket channel authorization
The system SHALL define private channel authorization rules in routes/channels.php for player, narrator, werewolf, and room channels.

#### Scenario: Player channel authorized by ID match
- **WHEN** a user subscribes to player.{playerId}
- **THEN** authorization succeeds only if user exists and user->id equals (int)playerId

#### Scenario: Narrator channel authorized by room and narrator status
- **WHEN** a user subscribes to narrator.{roomId}
- **THEN** authorization succeeds only if user exists, user->room_id equals (int)roomId, and user->is_narrator is true

#### Scenario: Werewolf channel authorized by room and faction
- **WHEN** a user subscribes to werewolves.{roomId}
- **THEN** authorization succeeds only if user exists, user->room_id equals (int)roomId, and user->role->faction is 'werewolves'

#### Scenario: Room channel authorized by room membership
- **WHEN** a user subscribes to room.{roomId}
- **THEN** authorization succeeds only if user exists and user->room_id equals (int)roomId
