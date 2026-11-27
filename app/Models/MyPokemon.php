<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MyPokemon extends Model
{
    protected $table = 'my_pokemons';

    protected $fillable = [
        'user_id', 'battle_team_id', 'title', 'notes', 'image_url',
        'nama_pokemon', 'nomor_urut', 'peran_tim',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'battle_team_id' => 'integer',
        'nomor_urut' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function battleTeam(): BelongsTo
    {
        return $this->belongsTo(BattleTeam::class);
    }
}
