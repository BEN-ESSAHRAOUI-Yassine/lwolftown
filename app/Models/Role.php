<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'key',
        'description',
        'faction',
        'night_order',
        'abilities',
        'win_condition',
    ];

    protected $casts = [
        'night_order' => 'integer',
        'abilities' => 'array',
    ];

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }
}
