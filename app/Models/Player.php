<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Player extends Model
{
    protected $fillable = [
        'room_id',
        'nickname',
        'session_token',
        'role_id',
        'is_alive',
        'is_host',
        'is_narrator',
        'voting_banned',
        'is_silenced',
        'is_slave',
        'master_id',
        'seat_position',
    ];

    protected $casts = [
        'is_alive' => 'boolean',
        'is_host' => 'boolean',
        'is_narrator' => 'boolean',
        'voting_banned' => 'boolean',
        'is_silenced' => 'boolean',
        'is_slave' => 'boolean',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function nightActions(): HasMany
    {
        return $this->hasMany(NightAction::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class, 'voter_id');
    }

    public function coupleBond(): HasOne
    {
        return $this->hasOne(CoupleBond::class, 'player_id');
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'master_id');
    }

    public function slaves(): HasMany
    {
        return $this->hasMany(Player::class, 'master_id');
    }
}
