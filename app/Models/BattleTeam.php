<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BattleTeam extends Model
{
    public const DEFAULTS = ['Tim Utama', 'Tim Latihan', 'Cadangan'];

    protected $fillable = ['user_id', 'name'];

    protected $casts = ['user_id' => 'integer'];

    public static function createDefaultsFor(int $userId): void
    {
        foreach (self::DEFAULTS as $name) {
            self::firstOrCreate(['user_id' => $userId, 'name' => $name]);
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function myPokemons(): HasMany
    {
        return $this->hasMany(MyPokemon::class);
    }
}
