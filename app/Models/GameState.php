<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameState extends Model
{
    protected $fillable = [
        'room_id',
        'phase',
        'round',
        'data',
    ];

    protected $casts = [
        'round' => 'integer',
        'data' => 'array',
    ];

    public static function defaultData(): array
    {
        return [
            'seat_order' => [],
            'enchanted_player_ids' => [],
            'wolf_father_used' => false,
            'elder_first_attack_survived' => false,
            'elder_abilities_disabled' => false,
            'fox_ability_active' => true,
            'bear_tamer_alive' => true,
            'infected_werewolf_id' => null,
            'wolf_hound_choice' => null,
            'white_werewolf_solo_night' => 0,
            'stuttering_judge_used' => false,
            'second_vote_triggered' => false,
            'pied_piper_eliminated' => false,
            'vote_ban_next_round' => [],
            'bodyguard_protected_ids' => [],
            'bodyguard_last_protected_id' => null,
            'witch_save_used' => false,
            'witch_poison_used' => false,
            'devoted_servant_used' => false,
            'knight_killed_by_werewolf' => false,
            'players_ready' => [],
            'action_history' => [],
            'seer_results' => [],
            'fox_results' => [],
            'lover_info' => [],
            'last_night_deaths' => [],
            'winning_faction' => null,
            'scapegoat_eliminated_by_tie' => false,
            'angel_eliminated_by_vote' => false,
            'kira_remaining_guesses' => 3,
            'kira_correct_count' => 0,
            'kira_correct_targets' => [],
            'master_slave_ids' => [],
            'silenced_player_ids' => [],
            'silencer_ability_count' => 1,
            'defense_window_open' => false,
            'defense_player_ids' => [],
            'vote_phase' => 'initial',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function nightActions(): HasMany
    {
        return $this->hasMany(NightAction::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function coupleBonds(): HasMany
    {
        return $this->hasMany(CoupleBond::class);
    }
}
