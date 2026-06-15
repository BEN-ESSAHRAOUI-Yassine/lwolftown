# Database Schema Specification

## Purpose
Defines the database schema for all core tables in the Loup-Garou companion app, including column definitions, constraints, foreign keys, and default values.

## Requirements

### Requirement: Rooms table migration
The system SHALL create a rooms table with columns for code, host_player_id, status, narration_mode, night_mode, and settings JSON.

#### Scenario: Rooms table structure
- **WHEN** the rooms migration runs
- **THEN** the table has columns: id (bigint PK), code (varchar 6, unique), host_player_id (bigint FK nullable), status (varchar, default 'waiting'), narration_mode (varchar, default 'human'), night_mode (varchar, default 'narrator_driven'), settings (json nullable), created_at, updated_at

#### Scenario: Room code is unique
- **WHEN** two rooms are created with the same code
- **THEN** the second insert fails with a unique constraint violation

### Requirement: Players table migration
The system SHALL create a players table with columns for room_id, nickname, session_token, role_id, and all boolean flags.

#### Scenario: Players table structure
- **WHEN** the players migration runs
- **THEN** the table has columns: id (bigint PK), room_id (bigint FK), nickname (varchar), session_token (varchar, unique), role_id (bigint FK nullable), is_alive (boolean, default true), is_host (boolean, default false), is_narrator (boolean, default false), voting_banned (boolean, default false), is_silenced (boolean, default false), is_slave (boolean, default false), master_id (bigint FK nullable), seat_position (int nullable), created_at, updated_at

#### Scenario: Session token is unique
- **WHEN** two players are created with the same session_token
- **THEN** the second insert fails with a unique constraint violation

#### Scenario: is_alive defaults to true
- **WHEN** a player is created without specifying is_alive
- **THEN** is_alive is true

### Requirement: Roles table migration
The system SHALL create a roles table with columns for key, description, faction, night_order, abilities JSON, and win_condition.

#### Scenario: Roles table structure
- **WHEN** the roles migration runs
- **THEN** the table has columns: id (bigint PK), key (varchar, unique), description (text nullable), faction (varchar), night_order (int nullable), abilities (json nullable), win_condition (varchar), created_at, updated_at

#### Scenario: Role key is unique
- **WHEN** two roles are created with the same key
- **THEN** the second insert fails with a unique constraint violation

### Requirement: Game states table migration
The system SHALL create a game_states table with a unique room_id FK, phase, round, and data JSON.

#### Scenario: Game states table structure
- **WHEN** the game_states migration runs
- **THEN** the table has columns: id (bigint PK), room_id (bigint FK, unique), phase (varchar, default 'waiting'), round (int, default 1), data (json nullable), created_at, updated_at

#### Scenario: One game state per room
- **WHEN** two game states are created for the same room_id
- **THEN** the second insert fails with a unique constraint violation

### Requirement: Night actions table migration
The system SHALL create a night_actions table with game_state_id, player_id, action_type, target_id, metadata JSON, and resolved_at.

#### Scenario: Night actions table structure
- **WHEN** the night_actions migration runs
- **THEN** the table has columns: id (bigint PK), game_state_id (bigint FK), player_id (bigint FK), action_type (varchar), target_id (bigint FK nullable), metadata (json nullable), resolved_at (timestamp nullable), created_at, updated_at

#### Scenario: Unresolved action has null resolved_at
- **WHEN** a night action is created
- **THEN** resolved_at is null by default

### Requirement: Votes table migration
The system SHALL create a votes table with game_state_id, voter_id, target_id, and round_type.

#### Scenario: Votes table structure
- **WHEN** the votes migration runs
- **THEN** the table has columns: id (bigint PK), game_state_id (bigint FK), voter_id (bigint FK), target_id (bigint FK), round_type (varchar, default 'initial'), created_at, updated_at

### Requirement: Couple bonds table migration
The system SHALL create a couple_bonds table with game_state_id, player_id, and partner_id.

#### Scenario: Couple bonds table structure
- **WHEN** the couple_bonds migration runs
- **THEN** the table has columns: id (bigint PK), game_state_id (bigint FK), player_id (bigint FK), partner_id (bigint FK), created_at, updated_at

### Requirement: GameState data JSON default keys
The system SHALL initialize GameState.data JSON with all 33 default keys as specified in scratch.md Section 4.

#### Scenario: All default keys present on creation
- **WHEN** a GameState is created
- **THEN** data contains keys: seat_order, enchanted_player_ids, wolf_father_used, elder_first_attack_survived, elder_abilities_disabled, fox_ability_active, bear_tamer_alive, infected_werewolf_id, wolf_hound_choice, white_werewolf_solo_night, stuttering_judge_used, second_vote_triggered, pied_piper_eliminated, vote_ban_next_round, bodyguard_protected_ids, bodyguard_last_protected_id, witch_save_used, witch_poison_used, devoted_servant_used, knight_killed_by_werewolf, players_ready, action_history, seer_results, fox_results, lover_info, last_night_deaths, winning_faction, scapegoat_eliminated_by_tie, angel_eliminated_by_vote, kira_remaining_guesses, kira_correct_count, kira_correct_targets, master_slave_ids, silenced_player_ids, silencer_ability_count, defense_window_open, defense_player_ids, vote_phase

#### Scenario: Boolean defaults correct
- **WHEN** a GameState is created
- **THEN** wolf_father_used is false, elder_first_attack_survived is false, elder_abilities_disabled is false, fox_ability_active is true, bear_tamer_alive is true, stuttering_judge_used is false, second_vote_triggered is false, pied_piper_eliminated is false, witch_save_used is false, witch_poison_used is false, devoted_servant_used is false, knight_killed_by_werewolf is false, angel_eliminated_by_vote is false

#### Scenario: Numeric defaults correct
- **WHEN** a GameState is created
- **THEN** white_werewolf_solo_night is 0, kira_remaining_guesses is 3, kira_correct_count is 0

#### Scenario: Collection defaults correct
- **WHEN** a GameState is created
- **THEN** seat_order is [], enchanted_player_ids is [], bodyguard_protected_ids is [], players_ready is [], action_history is [], seer_results is {}, fox_results is {}, lover_info is {}, last_night_deaths is [], kira_correct_targets is [], master_slave_ids is [], silenced_player_ids is [], defense_player_ids is []

#### Scenario: Null defaults correct
- **WHEN** a GameState is created
- **THEN** infected_werewolf_id is null, wolf_hound_choice is null, bodyguard_last_protected_id is null, winning_faction is null, vote_phase is 'initial', defense_window_open is false, silencer_ability_count is 1

### Requirement: Migration compatibility
All migrations SHALL use SQL types compatible with both SQLite and PostgreSQL.

#### Scenario: No PostgreSQL-specific types
- **WHEN** migration files are inspected
- **THEN** no column uses PostgreSQL-specific types (e.g., jsonb, uuid, inet)

#### Scenario: Foreign keys defined
- **WHEN** migrations run
- **THEN** all FK columns have corresponding foreign key constraints
