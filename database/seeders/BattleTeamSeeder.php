<?php

namespace Database\Seeders;

use App\Models\BattleTeam;
use Illuminate\Database\Seeder;

class BattleTeamSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Tim Utama', 'Tim Latihan', 'Cadangan'] as $nama) {
            BattleTeam::firstOrCreate(['nama_tim' => $nama]);
        }
    }
}
