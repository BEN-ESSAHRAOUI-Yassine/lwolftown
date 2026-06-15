# Eloquent Models Specification

## Purpose
Defines the Eloquent models, their fillable fields, attribute casts, relationships, and seeders for all core database tables in the Loup-Garou companion app.

## Requirements

### Requirement: Room model
The system SHALL include an Eloquent model for the rooms table with correct fillable, casts, route key, and relationships.

#### Scenario: Room fillable fields
- **WHEN** Room model is loaded
- **THEN** fillable contains: code, host_player_id, status, narration_mode, night_mode, settings

#### Scenario: Room casts
- **WHEN** Room model is loaded
- **THEN** settings is cast to array

#### Scenario: Room route key
- **WHEN** Route::modelBinding is used for Room
- **THEN** the route key is 'code'

#### Scenario: Room relationships
- **WHEN** Room model is loaded
- **THEN** it has players (hasMany Player), host (belongsTo Player via host_player_id), and gameState (hasOne GameState)

### Requirement: Player model
The system SHALL include an Eloquent model for the players table with correct fillable, casts, and relationships.

#### Scenario: Player fillable fields
- **WHEN** Player model is loaded
- **THEN** fillable contains: room_id, nickname, session_token, role_id, is_alive, is_host, is_narrator, voting_banned, is_silenced, is_slave, master_id, seat_position

#### Scenario: Player boolean casts
- **WHEN** Player model is loaded
- **THEN** is_alive, is_host, is_narrator, voting_banned, is_silenced, is_slave are all cast to boolean

#### Scenario: Player relationships
- **WHEN** Player model is loaded
- **THEN** it has room (belongsTo Room), role (belongsTo Role), nightActions (hasMany NightAction), votes (hasMany Vote via voter_id), coupleBond (hasOne CoupleBond via player_id), master (belongsTo Player via master_id), slaves (hasMany Player via master_id)

### Requirement: Role model
The system SHALL include an Eloquent model for the roles table with correct fillable, casts, and relationships.

#### Scenario: Role fillable fields
- **WHEN** Role model is loaded
- **THEN** fillable contains: key, description, faction, night_order, abilities, win_condition

#### Scenario: Role casts
- **WHEN** Role model is loaded
- **THEN** night_order is cast to integer, abilities is cast to array

#### Scenario: Role relationships
- **WHEN** Role model is loaded
- **THEN** it has players (hasMany Player)

### Requirement: GameState model
The system SHALL include an Eloquent model for the game_states table with correct fillable, casts, and relationships.

#### Scenario: GameState fillable fields
- **WHEN** GameState model is loaded
- **THEN** fillable contains: room_id, phase, round, data

#### Scenario: GameState casts
- **WHEN** GameState model is loaded
- **THEN** round is cast to integer, data is cast to array

#### Scenario: GameState relationships
- **WHEN** GameState model is loaded
- **THEN** it has room (belongsTo Room), nightActions (hasMany NightAction), votes (hasMany Vote), coupleBonds (hasMany CoupleBond)

#### Scenario: GameState default data
- **WHEN** GameState is created
- **THEN** data is initialized with all 33 default keys from scratch.md Section 4

### Requirement: NightAction model
The system SHALL include an Eloquent model for the night_actions table with correct fillable and casts.

#### Scenario: NightAction fillable fields
- **WHEN** NightAction model is loaded
- **THEN** fillable contains: game_state_id, player_id, action_type, target_id, metadata, resolved_at

#### Scenario: NightAction casts
- **WHEN** NightAction model is loaded
- **THEN** metadata is cast to array, resolved_at is cast to datetime

### Requirement: Vote model
The system SHALL include an Eloquent model for the votes table with correct fillable and relationships.

#### Scenario: Vote fillable fields
- **WHEN** Vote model is loaded
- **THEN** fillable contains: game_state_id, voter_id, target_id, round_type

#### Scenario: Vote relationships
- **WHEN** Vote model is loaded
- **THEN** it has gameState (belongsTo GameState), voter (belongsTo Player via voter_id), target (belongsTo Player via target_id)

### Requirement: CoupleBond model
The system SHALL include an Eloquent model for the couple_bonds table with correct fillable.

#### Scenario: CoupleBond fillable fields
- **WHEN** CoupleBond model is loaded
- **THEN** fillable contains: game_state_id, player_id, partner_id

### Requirement: RoleSeeder
The system SHALL include a RoleSeeder that seeds all 27 roles with correct data from scratch.md Section 6.

#### Scenario: All 27 roles seeded
- **WHEN** php artisan db:seed runs
- **THEN** 27 roles exist in the roles table

#### Scenario: Village roles seeded correctly
- **WHEN** seeder runs
- **THEN** villager (null order), seer (order 10), witch (order 11), hunter (null), bodyguard (order 8), little_girl (order 9), cupid (order 0), elder (null), scapegoat (null), village_idiot (null), two_sisters (null), three_brothers (null), stuttering_judge (null), knight_with_rusty_sword (null), devoted_servant (null), bear_tamer (order 14), fox (order 13), the_master (order 0) all exist with faction 'village'

#### Scenario: Werewolf roles seeded correctly
- **WHEN** seeder runs
- **THEN** werewolf (order 5), big_bad_wolf (order 6), accursed_wolf_father (order 4), white_werewolf (order 7), wolf_hound (order 3), silencer (order 2) all exist with faction 'werewolves'

#### Scenario: Neutral roles seeded correctly
- **WHEN** seeder runs
- **THEN** angel (null), pied_piper (order 12), kira (order 15) all exist with faction 'neutral'

#### Scenario: Seeder is idempotent
- **WHEN** php artisan db:seed runs twice
- **THEN** no duplicate roles are created (updateOrCreate on key)

### Requirement: DatabaseSeeder
The system SHALL include a DatabaseSeeder that calls RoleSeeder.

#### Scenario: DatabaseSeeder calls RoleSeeder
- **WHEN** php artisan db:seed runs
- **THEN** RoleSeeder is invoked by DatabaseSeeder
