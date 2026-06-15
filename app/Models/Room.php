<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Room extends Model
{
    protected $fillable = [
        'code',
        'host_player_id',
        'status',
        'narration_mode',
        'night_mode',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'host_player_id');
    }

    public function gameState(): HasOne
    {
        return $this->hasOne(GameState::class);
    }
}
