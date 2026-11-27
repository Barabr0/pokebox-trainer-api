<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'user_id' => fn (Blueprint $t) => $t->unsignedBigInteger('user_id')->nullable()->after('id'),
            'title' => fn (Blueprint $t) => $t->string('title', 100)->nullable()->after('battle_team_id'),
            'notes' => fn (Blueprint $t) => $t->text('notes')->nullable()->after('title'),
            'image_url' => fn (Blueprint $t) => $t->string('image_url', 500)->nullable()->after('notes'),
        ];

        foreach ($columns as $name => $add) {
            if (! Schema::hasColumn('my_pokemons', $name)) {
                Schema::table('my_pokemons', fn (Blueprint $table) => $add($table));
            }
        }

        // Pemilik item mengikuti pemilik timnya
        DB::table('battle_teams')->select('id', 'user_id')->orderBy('id')->get()
            ->each(fn ($team) => DB::table('my_pokemons')
                ->where('battle_team_id', $team->id)
                ->whereNull('user_id')
                ->update(['user_id' => $team->user_id]));

        // Judul data lama diisi nama Pokemon
        DB::table('my_pokemons')->whereNull('title')
            ->update(['title' => DB::raw('nama_pokemon')]);

        Schema::table('my_pokemons', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->string('title', 100)->nullable(false)->change();
        });

        if (! collect(Schema::getForeignKeys('my_pokemons'))->contains(fn ($fk) => $fk['columns'] === ['user_id'])) {
            Schema::table('my_pokemons', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('my_pokemons', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'title', 'notes', 'image_url']);
        });
    }
};
