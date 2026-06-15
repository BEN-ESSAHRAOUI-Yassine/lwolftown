<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoupleBond extends Model
{
    protected $fillable = [
        'game_state_id',
        'player_id',
        'partner_id',
    ];

    public function gameState(): BelongsTo
    {
        return $this->belongsTo(GameState::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'partner_id');
    }
}
