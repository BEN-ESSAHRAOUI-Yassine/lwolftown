<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    protected $fillable = [
        'game_state_id',
        'voter_id',
        'target_id',
        'round_type',
    ];

    public function gameState(): BelongsTo
    {
        return $this->belongsTo(GameState::class);
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'voter_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'target_id');
    }
}
