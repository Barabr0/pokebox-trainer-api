<?php

namespace Database\Seeders;

use App\Models\BattleTeam;
use App\Models\User;
use Illuminate\Database\Seeder;

// Membuat 3 tim bawaan untuk akun yang sudah ada
class BattleTeamSeeder extends Seeder
{
    public function run(): void
    {
        foreach (User::all() as $user) {
            BattleTeam::createDefaultsFor($user->id);
        }
    }
}
