<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BattleTeam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BattleTeamController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => BattleTeam::withCount('myPokemons')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama_tim' => ['required', 'string', 'max:100', 'unique:battle_teams,nama_tim'],
        ]);

        return response()->json(['data' => BattleTeam::create($data)], 201);
    }

    public function show(BattleTeam $battleTeam): JsonResponse
    {
        return response()->json(['data' => $battleTeam->load('myPokemons')]);
    }

    public function update(Request $request, BattleTeam $battleTeam): JsonResponse
    {
        $data = $request->validate([
            'nama_tim' => [
                'required', 'string', 'max:100',
                Rule::unique('battle_teams', 'nama_tim')->ignore($battleTeam->id),
            ],
        ]);

        $battleTeam->update($data);

        return response()->json(['data' => $battleTeam]);
    }

    // Menghapus tim juga menghapus semua Pokemon di dalamnya (cascade)
    public function destroy(BattleTeam $battleTeam): JsonResponse
    {
        $battleTeam->delete();

        return response()->json(['message' => 'Tim berhasil dihapus.']);
    }
}
