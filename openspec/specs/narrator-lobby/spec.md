# narrator-lobby

The NarratorLobby is the pre-game control panel where the narrator configures the game before starting. It provides live player monitoring, role composition, night order, difficulty settings, and game launch.

## Purpose

Provide the narrator with a single interface to manage all pre-game configuration, monitor player joins, and launch the game when ready.

## Requirements

### Requirement: NarratorLobby component
The system SHALL provide a NarratorLobby Livewire component accessible at /room/{room}/narrator that displays QR code, live player list, seat order, role composition, night order, difficulty settings, information disclosure, presets, and a start game button.

#### Scenario: Narrator sees QR code
- **WHEN** narrator navigates to /room/{room}/narrator
- **THEN** a QR code is displayed pointing to the join URL (APP_URL . '/join/' . $room->code)

#### Scenario: Room code displayed
- **WHEN** narrator views the lobby
- **THEN** the room code is displayed in Cinzel font below the QR code

### Requirement: Live player list
The system SHALL display a live player list that polls every 3 seconds and shows each player's nickname, joined time, and a [Kick] button.

#### Scenario: Player list updates on join
- **WHEN** a new player joins the room
- **THEN** the player list updates within 3 seconds to show the new player

#### Scenario: Kick player
- **WHEN** narrator clicks [Kick] on a player
- **THEN** the player record is deleted and a PlayerLeft event fires on room.{id}

### Requirement: Seat order drag-and-drop
The system SHALL provide a drag-and-drop interface to arrange players into circular seat positions. The seat order is saved to the room settings JSON.

#### Scenario: Seat order saved
- **WHEN** narrator drags players into seat positions
- **THEN** the seat_order array in room settings is updated with player_ids in the new order

#### Scenario: Circular preview
- **WHEN** seat order is set
- **THEN** a visual circular seating preview is displayed showing player positions

### Requirement: Role composition panel
The system SHALL display all 27 roles grouped by faction (Village/Werewolf/Neutral) with +/- counters for each role. Hard validations are enforced live.

#### Scenario: Role counter increment
- **WHEN** narrator clicks + on a role
- **THEN** the role count increments by 1

#### Scenario: Role counter decrement
- **WHEN** narrator clicks - on a role and count > 0
- **THEN** the role count decrements by 1

#### Scenario: Two Sisters validation
- **WHEN** role composition has exactly 1 two_sisters
- **THEN** a validation error is shown: 'Two Sisters must be exactly 0 or 2'

#### Scenario: Three Brothers validation
- **WHEN** role composition has exactly 1 or 2 three_brothers
- **THEN** a validation error is shown: 'Three Brothers must be exactly 0 or 3'

#### Scenario: Solo roles validation
- **WHEN** role composition has more than 1 of any solo role (cupid, kira, angel, pied_piper, etc.)
- **THEN** a validation error is shown: 'Solo roles maximum 1 each'

#### Scenario: Total roles match player count
- **WHEN** total role count does not equal non-narrator player count
- **THEN** a validation error is shown: 'Total roles must equal player count'

#### Scenario: Start Game button disabled
- **WHEN** any validation error exists
- **THEN** the [Start Game] button is disabled

### Requirement: Night order drag-and-drop
The system SHALL provide a drag-and-drop reorderable list of active roles for night order. Default order follows scratch.md Section 7.

#### Scenario: Night order saved
- **WHEN** narrator reorders night roles
- **THEN** the night_order array in room settings is updated

#### Scenario: Reset to default
- **WHEN** narrator clicks [Reset to Default]
- **THEN** the night order resets to the default order from scratch.md Section 7

### Requirement: Difficulty settings
The system SHALL provide difficulty setting toggles: Night Mode (Narrator-Driven/Simultaneous), Silencer vote ban (on/off), Bear Tamer (Public growl/Narrator only), Kira (Unknown death/Hidden completely).

#### Scenario: Night mode toggle
- **WHEN** narrator toggles night mode
- **THEN** the night_mode setting is updated in room settings

#### Scenario: Silencer vote ban toggle
- **WHEN** narrator toggles silencer vote ban
- **THEN** the silencer_vote_ban setting is updated in room settings

### Requirement: Information disclosure
The system SHALL provide per-faction toggles to show/hide role lists to players before game starts.

#### Scenario: Faction disclosure toggle
- **WHEN** narrator toggles faction disclosure
- **THEN** the disclosure_settings for that faction are updated in room settings

### Requirement: Preset save/load
The system SHALL allow the narrator to save the current role composition as a named preset and load presets from a dropdown.

#### Scenario: Save preset
- **WHEN** narrator clicks [Save as Preset] and enters a name
- **THEN** the current role composition is saved to room settings presets array

#### Scenario: Load preset
- **WHEN** narrator selects a preset from the dropdown
- **THEN** the role composition is loaded from the preset and can be edited

### Requirement: Start game
The system SHALL provide a [Start Game] button that validates the game can start and calls RoleAssignmentService::assign(Room), then redirects to /game/{room}/narrator.

#### Scenario: Start game success
- **WHEN** narrator clicks [Start Game] and all validations pass
- **THEN** RoleAssignmentService::assign(Room) is called and narrator is redirected to /game/{room}/narrator

#### Scenario: Start game validation fails
- **WHEN** narrator clicks [Start Game] and validations fail
- **THEN** the button remains disabled and validation errors are shown

### Requirement: PlayerLeft event
The system SHALL provide a PlayerLeft event class that implements ShouldBroadcast and broadcasts on the room.{roomId} private channel with player_id and player_count.

#### Scenario: Event broadcasts on kick
- **WHEN** a player is kicked from the lobby
- **THEN** PlayerLeft event broadcasts on room.{roomId} with player_id and player_count
