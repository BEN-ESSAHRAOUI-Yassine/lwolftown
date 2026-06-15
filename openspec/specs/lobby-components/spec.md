# Lobby Components Specification

## Purpose
Defines the Livewire components, views, routes, and controller for the lobby create/join flow in the Loup-Garou companion app.

## Requirements

### Requirement: CreateRoom Livewire component
The system SHALL provide a CreateRoom Livewire component with a $nickname property and a submit() method that calls LobbyService::createRoom and performs a JavaScript redirect to /room/{code}/narrator.

#### Scenario: Submit creates room and redirects
- **WHEN** user enters nickname 'Alice' and submits
- **THEN** a room is created, the user is assigned as host narrator, and a JS redirect to /room/{code}/narrator occurs

#### Scenario: Nickname validation
- **WHEN** user submits with empty nickname
- **THEN** a validation error is returned for the nickname field

### Requirement: JoinRoom Livewire component
The system SHALL provide a JoinRoom Livewire component with $code and $nickname properties and a submit() method that calls LobbyService::joinRoom and performs a JavaScript redirect to /room/{code}/player.

#### Scenario: Submit joins room and redirects
- **WHEN** user enters code 'ABC123' and nickname 'Bob' and submits
- **THEN** the user joins the room, receives a session_token cookie, and a JS redirect to /room/{code}/player occurs

#### Scenario: Code validation
- **WHEN** user submits with empty code
- **THEN** a validation error is returned for the code field

#### Scenario: Room not found
- **WHEN** user submits with code 'XXXXXX' that doesn't exist
- **THEN** a validation error is returned for the code field

### Requirement: Welcome view
The system SHALL provide a welcome.blade.php view with a language toggle (EN/FR) and links to create and join rooms.

#### Scenario: Language toggle present
- **WHEN** welcome page loads
- **THEN** EN and FR toggle links are visible

#### Scenario: Create link present
- **WHEN** welcome page loads
- **THEN** a link to /create is visible

#### Scenario: Join link present
- **WHEN** welcome page loads
- **THEN** a link to /join is visible

### Requirement: Lobby routes
The system SHALL define the following routes in web.php: GET /create (CreateRoom Livewire), GET /join/{code?} (JoinRoom Livewire), POST /api/rooms (LobbyController@create), POST /api/rooms/join (LobbyController@join), GET /locale/{locale} (locale switcher redirect).

#### Scenario: GET /create returns CreateRoom component
- **WHEN** a GET request is made to /create
- **THEN** the CreateRoom Livewire component is rendered

#### Scenario: GET /join returns JoinRoom component
- **WHEN** a GET request is made to /join
- **THEN** the JoinRoom Livewire component is rendered

#### Scenario: GET /join/ABC123 pre-fills code
- **WHEN** a GET request is made to /join/ABC123
- **THEN** the JoinRoom Livewire component is rendered with code pre-filled

#### Scenario: POST /api/rooms creates room
- **WHEN** a POST request is made to /api/rooms with valid nickname and locale
- **THEN** a room is created and the response redirects to /room/{code}/narrator

#### Scenario: POST /api/rooms/join joins room
- **WHEN** a POST request is made to /api/rooms/join with valid code and nickname
- **THEN** the player joins the room and the response redirects to /room/{code}/player

### Requirement: LobbyController thin controller
The system SHALL provide a LobbyController with create() and join() methods that validate input, delegate to LobbyService, and return redirects. Controllers SHALL NOT contain business logic.

#### Scenario: Controller delegates to service
- **WHEN** POST /api/rooms is called
- **THEN** LobbyController::create validates input and calls LobbyService::createRoom

#### Scenario: Controller returns redirect on success
- **WHEN** LobbyService::createRoom succeeds
- **THEN** the response is a redirect to /room/{code}/narrator

#### Scenario: Controller returns validation error on failure
- **WHEN** LobbyService::joinRoom throws a validation exception
- **THEN** the response returns the validation error message
