## 1. Event & Route

- [x] 1.1 Create PlayerLeft event class (ShouldBroadcast, broadcasts on room.{id} channel, payload: player_id + player_count)
- [x] 1.2 Add GET /room/{room}/narrator route pointing to NarratorLobby Livewire component

## 2. NarratorLobby Component — Core

- [x] 2.1 Create NarratorLobby Livewire component class with properties: $room, $players, $roleComposition, $nightOrder, $seatOrder, $difficultySettings, $disclosureSettings, $presets, $validationErrors
- [x] 2.2 Implement mount() to load room and initialize all properties from room settings JSON
- [x] 2.3 Implement polling: protected $listeners = ['poll' => 'loadPlayers']; poll every 3s
- [x] 2.4 Implement loadPlayers() method to refresh $players collection

## 3. QR Code & Player List

- [x] 3.1 Add QR code display using QrHelper::generate(APP_URL . '/join/' . $room->code)
- [x] 3.2 Display room code in Cinzel font below QR
- [x] 3.3 Render player list with nickname, joined time, and [Kick] button
- [x] 3.4 Implement kickPlayer($playerId) method: delete player record, fire PlayerLeft event, refresh list

## 4. Seat Order

- [x] 4.1 Implement seat order drag-and-drop (Alpine.js sortable or wire:sortable)
- [x] 4.2 Save seat order to room->settings['seat_order'] on reorder
- [x] 4.3 Display circular seating preview with player nicknames

## 5. Role Composition

- [x] 5.1 Load all 27 roles grouped by faction (Village/Werewolf/Neutral) from Role model
- [x] 5.2 Render role cards with name, description tooltip, +/- counters
- [x] 5.3 Implement incrementRole($roleKey) and decrementRole($roleKey) methods
- [x] 5.4 Implement validateRoleComposition() — Two Sisters, Three Brothers, solo roles, total count
- [x] 5.5 Show inline validation errors per role
- [x] 5.6 Disable [Start Game] button when validation errors exist

## 6. Night Order

- [x] 6.1 Load default night order from scratch.md Section 7
- [x] 6.2 Render reorderable list of active roles with night_order number + role name
- [x] 6.3 Implement drag-and-drop reorder (Alpine.js sortable or wire:sortable)
- [x] 6.4 Save night order to room->settings['night_order'] on reorder
- [x] 6.5 Implement resetNightOrder() to restore default order

## 7. Difficulty & Disclosure Settings

- [x] 7.1 Render difficulty toggles: Night Mode, Silencer vote ban, Bear Tamer, Kira
- [x] 7.2 Implement toggleDifficulty($key) method to update room settings
- [x] 7.3 Render information disclosure toggles per faction
- [x] 7.4 Implement toggleDisclosure($faction) method to update room settings

## 8. Presets

- [x] 8.1 Implement savePreset($name) method: save current role_composition to room->settings['presets']
- [x] 8.2 Implement loadPreset($index) method: load role_composition from preset
- [x] 8.3 Render preset dropdown and [Save as Preset] button

## 9. Start Game

- [x] 9.1 Implement startGame() method: call RoleAssignmentService::assign($room), redirect to /game/{room}/narrator
- [x] 9.2 Wire [Start Game] button to startGame() with validation gate

## 10. Views

- [x] 10.1 Create narrator-lobby.blade.php: layout with QR panel, player list, seat order, role composition, night order, difficulty, disclosure, presets, start button

## 11. Verification

- [x] 11.1 Test: QR code generates correctly and points to join URL
- [x] 11.2 Test: Role +/- counters work with live validation
- [x] 11.3 Test: Drag-and-drop seat order saves correctly
- [x] 11.4 Test: Night order drag-and-drop saves correctly
- [x] 11.5 Test: [Start Game] blocked until all validations pass
- [x] 11.6 Test: Kick player removes from list and fires PlayerLeft
