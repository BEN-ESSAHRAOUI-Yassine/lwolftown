<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NightAction extends Model
{
    protected $fillable = [
        'game_state_id',
        'player_id',
        'action_type',
        'target_id',
        'metadata',
        'resolved_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function gameState(): BelongsTo
    {
        return $this->belongsTo(GameState::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'target_id');
    }
}
