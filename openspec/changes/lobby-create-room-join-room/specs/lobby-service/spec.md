## ADDED Requirements

### Requirement: Create room
The system SHALL provide a LobbyService::createRoom(nickname, locale) method that generates a unique 6-character uppercase room code, creates a Room record with status 'waiting', creates a host Player with is_narrator=true and is_host=true, stores the session_token in an httpOnly cookie, stores the locale in the session, and returns the Room.

#### Scenario: Room created with unique code
- **WHEN** createRoom('Alice', 'en') is called
- **THEN** a Room record exists with a unique 6-char uppercase code and status 'waiting'

#### Scenario: Host player is narrator
- **WHEN** createRoom('Alice', 'en') is called
- **THEN** a Player record exists with is_narrator=true, is_host=true, nickname='Alice', and belongs to the created room

#### Scenario: Session token stored in cookie
- **WHEN** createRoom('Alice', 'en') is called
- **THEN** the response contains an httpOnly cookie named 'session_token' with the player's session_token value

#### Scenario: Locale stored in session
- **WHEN** createRoom('Alice', 'fr') is called
- **THEN** the session contains locale='fr'

#### Scenario: Room code uniqueness
- **WHEN** createRoom is called twice
- **THEN** both rooms have different codes

### Requirement: Join room
The system SHALL provide a LobbyService::joinRoom(Room, nickname, Request) method that validates the room status is 'waiting', validates the nickname is not duplicate within the room, validates the player count does not exceed 24, creates a Player with is_narrator=false, stores the session_token in an httpOnly cookie, fires a PlayerJoined event on room.{id}, and returns the Player.

#### Scenario: Player joins waiting room
- **WHEN** joinRoom(room, 'Bob', request) is called and room status is 'waiting'
- **THEN** a Player record exists with nickname='Bob', is_narrator=false, and belongs to the room

#### Scenario: Session token stored in cookie
- **WHEN** joinRoom is called
- **THEN** the response contains an httpOnly cookie named 'session_token' with the player's session_token value

#### Scenario: PlayerJoined event fired
- **WHEN** joinRoom is called successfully
- **THEN** a PlayerJoined event is broadcast on room.{roomId} channel with player data and player_count

#### Scenario: Reject if room not waiting
- **WHEN** joinRoom is called and room status is 'playing'
- **THEN** a validation exception is thrown with error 'room_not_waiting'

#### Scenario: Reject if nickname duplicate
- **WHEN** joinRoom is called and a player with that nickname already exists in the room
- **THEN** a validation exception is thrown with error 'nickname_taken'

#### Scenario: Reject if room full
- **WHEN** joinRoom is called and the room already has 24 players
- **THEN** a validation exception is thrown with error 'room_full'

### Requirement: Validate game start
The system SHALL provide a LobbyService::validateGameStart(Room) method that returns an array of validation errors. The array is empty if all validations pass.

#### Scenario: Minimum 4 players
- **WHEN** validateGameStart is called and the room has 3 non-narrator players
- **THEN** errors array contains 'not_enough_players'

#### Scenario: Role count matches player count
- **WHEN** validateGameStart is called and role composition has 5 roles but room has 7 non-narrator players
- **THEN** errors array contains 'role_count_mismatch'

#### Scenario: At least one werewolf faction role
- **WHEN** validateGameStart is called and role composition has no werewolf faction roles
- **THEN** errors array contains 'no_werewolves'

#### Scenario: Two Sisters exactly 0 or 2
- **WHEN** validateGameStart is called and role composition has exactly 1 two_sisters role
- **THEN** errors array contains 'two_sisters_count'

#### Scenario: Three Brothers exactly 0 or 3
- **WHEN** validateGameStart is called and role composition has exactly 2 three_brothers roles
- **THEN** errors array contains 'three_brothers_count'

#### Scenario: Solo roles max 1 each
- **WHEN** validateGameStart is called and role composition has 2 cupid roles
- **THEN** errors array contains 'solo_role_duplicate'

#### Scenario: All validations pass
- **WHEN** validateGameStart is called with valid player count, role count, faction presence, and solo role constraints
- **THEN** errors array is empty

### Requirement: PlayerJoined event
The system SHALL provide a PlayerJoined event class that implements ShouldBroadcast and broadcasts on the room.{roomId} private channel with player{id, nickname, is_narrator} and player_count.

#### Scenario: Event broadcasts to room channel
- **WHEN** PlayerJoined is fired for a player in room 42
- **THEN** the event broadcasts on private channel 'room.42'

#### Scenario: Event payload contains player data
- **WHEN** PlayerJoined is fired
- **THEN** broadcastWith returns player{id, nickname, is_narrator} and player_count

### Requirement: QrHelper utility
The system SHALL provide a QrHelper::generate(string $data): string method that returns an SVG QR code string for the given data URL.

#### Scenario: QR code generated for join URL
- **WHEN** QrHelper::generate('https://example.com/join/ABC123') is called
- **THEN** the return value is a valid SVG string containing the QR code
