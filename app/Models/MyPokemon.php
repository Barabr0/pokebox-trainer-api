<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MyPokemon extends Model
{
    // Wajib ditulis: Laravel menganggap "pokemon" tidak punya bentuk jamak
    protected $table = 'my_pokemons';

    protected $fillable = ['battle_team_id', 'nama_pokemon', 'nomor_urut', 'peran_tim'];

    protected $casts = [
        'battle_team_id' => 'integer',
        'nomor_urut' => 'integer',
    ];

    public function battleTeam(): BelongsTo
    {
        return $this->belongsTo(BattleTeam::class);
    }
}
