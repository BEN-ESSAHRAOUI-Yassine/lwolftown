## 1. Event & Helper

- [ ] 1.1 Create PlayerJoined event class (ShouldBroadcast, broadcasts on room.{id} channel, payload: player{id,nickname,is_narrator} + player_count)
- [ ] 1.2 Create QrHelper class with generate(string $data): string method using chillerlan/php-qrcode

## 2. LobbyService

- [ ] 2.1 Create LobbyService class in app/Game/Services/
- [ ] 2.2 Implement createRoom(nickname, locale): generate unique 6-char code, create Room, create host Player (is_narrator=true, is_host=true), set session_token cookie, set locale in session, return Room
- [ ] 2.3 Implement joinRoom(Room, nickname, Request): validate room status='waiting', validate nickname not duplicate, validate max 24 players, create Player, set session_token cookie, fire PlayerJoined event, return Player
- [ ] 2.4 Implement validateGameStart(Room): return errors[] — min 4 players, role count match, at least 1 werewolf, Two Sisters 0 or 2, Three Brothers 0 or 3, solo roles max 1

## 3. Controller

- [ ] 3.1 Create LobbyController with create() method: validate request, call LobbyService::createRoom, redirect to /room/{code}/narrator
- [ ] 3.2 Add join() method: validate request, call LobbyService::joinRoom, redirect to /room/{code}/player

## 4. Livewire Components

- [ ] 4.1 Create CreateRoom Livewire component: $nickname property, submit() calls LobbyService, JS redirect to /room/{code}/narrator
- [ ] 4.2 Create CreateRoom Blade view: nickname input, submit button, validation error display
- [ ] 4.3 Create JoinRoom Livewire component: $code + $nickname properties, submit() calls LobbyService, JS redirect to /room/{code}/player
- [ ] 4.4 Create JoinRoom Blade view: code input (pre-fill from route), nickname input, submit button, validation error display

## 5. Views & Routes

- [ ] 5.1 Create welcome.blade.php: language toggle (EN/FR), links to /create and /join
- [ ] 5.2 Add routes to web.php: GET /, GET /locale/{locale}, GET /create, GET /join/{code?}, POST /api/rooms, POST /api/rooms/join
- [ ] 5.3 Verify: GET / renders welcome view

## 6. Verification

- [ ] 6.1 Test: Room created with unique 6-char code
- [ ] 6.2 Test: Host player has is_narrator=true, is_host=true
- [ ] 6.3 Test: Joining player gets session_token cookie
- [ ] 6.4 Test: PlayerJoined event broadcasts to room.{id}
- [ ] 6.5 Test: Validation errors surface for duplicate nickname, full room, non-waiting room
- [ ] 6.6 Test: validateGameStart returns correct errors for each constraint
