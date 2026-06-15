## 1. Event Classes

- [x] 1.1 Create GameStarted event class (ShouldBroadcast, broadcasts on room.{id}, payload: room_id)
- [x] 1.2 Create RoleAssigned event class (ShouldBroadcast, broadcasts on player.{id}, payload: role_key, faction, night_order, abilities)

## 2. RoleAssignmentService Core

- [x] 2.1 Create RoleAssignmentService class with assign(Room) method
- [x] 2.2 Implement role pool building from room->settings['role_composition'] (look up Role by key, expand by count)
- [x] 2.3 Implement pool shuffle using Collection::shuffle()
- [x] 2.4 Implement role assignment: set player.role_id for each non-narrator player
- [x] 2.5 Implement seat position assignment from room->settings['seat_order'] array

## 3. GameState Creation

- [x] 3.1 Create GameState with phase='night', round=1, data=GameState::defaultData()
- [x] 3.2 Set data.seat_order from room->settings['seat_order']
- [x] 3.3 Set data.silencer_ability_count: 1 if player count ≤ 10, else 2

## 4. Special Notifications

- [x] 4.1 Implement Two Sisters notification: each sister receives partner nickname on player.{id}
- [x] 4.2 Implement Three Brothers notification: each brother receives both brothers' nicknames on player.{id}
- [x] 4.3 Implement Werewolf pack notification: each wolf receives packmates list on player.{id} (exclude wolf_hound)
- [x] 4.4 Implement Kira identity notification: send to narrator.{room_id} channel only

## 5. Events & Status

- [x] 5.1 Fire GameStarted event on room.{id} after assignment
- [x] 5.2 Fire RoleAssigned event on player.{id} for each player after assignment
- [x] 5.3 Update room status from 'waiting' to 'playing'

## 6. Validation

- [x] 6.1 Validate room status is 'waiting' before assignment
- [x] 6.2 Validate total roles equals non-narrator player count
- [x] 6.3 Validate at least 1 werewolf faction role exists

## 7. Integration

- [x] 7.1 Update NarratorLobby::startGame() to call RoleAssignmentService::assign() before redirect
- [x] 7.2 Update redirect path to /game/{code}/narrator

## 8. Verification

- [x] 8.1 Test: Role pool built correctly from settings
- [x] 8.2 Test: Roles assigned to all non-narrator players
- [x] 8.3 Test: GameState created with all default data keys
- [x] 8.4 Test: GameStarted event fires on room.{id}
- [x] 8.5 Test: RoleAssigned event fires on player.{id} for each player
- [x] 8.6 Test: Room status changes to 'playing'
