<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BattleTeam extends Model
{
    protected $fillable = ['nama_tim'];

    public function myPokemons(): HasMany
    {
        return $this->hasMany(MyPokemon::class);
    }
}
