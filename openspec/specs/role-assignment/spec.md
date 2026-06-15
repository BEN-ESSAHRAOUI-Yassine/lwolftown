# role-assignment

The RoleAssignmentService handles the complete game launch sequence, transforming lobby configuration into an active game state with assigned roles.

## Purpose

Randomly assign roles to players, create the initial GameState, and broadcast role information to all players via WebSocket.

## Requirements

### Requirement: Role assignment service
The system SHALL provide a RoleAssignmentService with an assign(Room) method that performs the complete game launch sequence in a single DB transaction.

#### Scenario: Successful assignment
- **WHEN** narrator clicks Start Game and role composition matches player count
- **THEN** a GameState is created with phase='night', round=1, and full default data, each non-narrator player receives a role, and room status changes to 'playing'

#### Scenario: Role pool built from settings
- **WHEN** RoleAssignmentService::assign(Room) is called
- **THEN** a role pool is built from room->settings['role_composition'] by looking up Role models by key, with count matching the configured number for each role

#### Scenario: Roles shuffled and assigned
- **WHEN** the role pool is built
- **THEN** the pool is shuffled randomly and one role is assigned to each non-narrator player by setting player.role_id

#### Scenario: Seat positions assigned
- **WHEN** roles are assigned
- **THEN** each player's seat_position is set from room->settings['seat_order'] array index

#### Scenario: Silencer ability count set
- **WHEN** GameState is created
- **THEN** data.silencer_ability_count is set to 1 if non-narrator player count ≤ 10, else 2

### Requirement: GameStarted event
The system SHALL fire a GameStarted event on room.{roomId} private channel when the game begins.

#### Scenario: Event broadcast
- **WHEN** RoleAssignmentService completes successfully
- **THEN** GameStarted event broadcasts on room.{roomId} with room_id in payload

### Requirement: RoleAssigned event
The system SHALL fire a RoleAssigned event on player.{playerId} private channel for each player with their role details.

#### Scenario: Each player receives role
- **WHEN** roles are assigned to all players
- **THEN** each player receives RoleAssigned on player.{id} with role_key, faction, night_order, and abilities

#### Scenario: Role assigned notification
- **WHEN** a player receives RoleAssigned
- **THEN** the payload contains role_key (string), faction (string), night_order (int|null), and abilities (array)

### Requirement: Two Sisters notification
The system SHALL notify each Two Sister player of their partner's nickname immediately after assignment.

#### Scenario: Two Sisters paired
- **WHEN** role assignment completes and two_sisters count is 2
- **THEN** each sister receives a notification on player.{id} with their partner's nickname

### Requirement: Three Brothers notification
The system SHALL notify each Three Brother player of both brothers' nicknames immediately after assignment.

#### Scenario: Three Brothers grouped
- **WHEN** role assignment completes and three_brothers count is 3
- **THEN** each brother receives a notification on player.{id} with both brothers' nicknames

### Requirement: Werewolf pack notification
The system SHALL notify all werewolf faction players of their packmates immediately after assignment.

#### Scenario: Wolf pack formed
- **WHEN** role assignment completes and werewolf faction players exist
- **THEN** each wolf receives a notification on player.{id} with the list of all other wolves' nicknames and roles

#### Scenario: Wolf Hound no pack notification
- **WHEN** role assignment completes and a wolf_hound exists
- **THEN** the wolf_hound does NOT receive pack notification (not until night 1 choice)

### Requirement: Kira identity to narrator only
The system SHALL send Kira's identity only to the narrator channel, never to players.

#### Scenario: Kira identity broadcast
- **WHEN** role assignment completes and a player has the kira role
- **THEN** a notification is sent on narrator.{room_id} with "Kira is [Nickname]" in the action feed

#### Scenario: Kira identity not leaked
- **WHEN** role assignment completes and a player has the kira role
- **THEN** no notification about Kira's identity is sent to any player channel

### Requirement: Room status transition
The system SHALL update room status from 'waiting' to 'playing' after successful assignment.

#### Scenario: Room becomes playing
- **WHEN** RoleAssignmentService completes successfully
- **THEN** room->status is set to 'playing' and saved

### Requirement: Assignment validation
The system SHALL validate that the game can start before attempting assignment.

#### Scenario: Role count mismatch
- **WHEN** total roles in composition does not equal non-narrator player count
- **THEN** an exception is thrown and no changes are made

#### Scenario: No werewolf faction
- **WHEN** role composition has zero werewolf faction roles
- **THEN** an exception is thrown and no changes are made

#### Scenario: Room not in waiting status
- **WHEN** room->status is not 'waiting'
- **THEN** an exception is thrown and no changes are made
