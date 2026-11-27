<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('my_pokemons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('battle_team_id')->constrained('battle_teams')->cascadeOnDelete();
            $table->string('nama_pokemon', 100);
            $table->unsignedInteger('nomor_urut');
            $table->string('peran_tim', 50);
            $table->timestamps();

            // Pokemon yang sama tidak boleh dobel dalam satu tim
            $table->unique(['battle_team_id', 'nomor_urut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('my_pokemons');
    }
};
