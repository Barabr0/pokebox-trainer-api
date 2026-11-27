<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BattleTeam;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BattleTeamController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->mine()->withCount('myPokemons')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['user_id'] = auth()->id();

        return response()->json(['data' => BattleTeam::create($data)], 201);
    }

    public function show(int $battleTeam): JsonResponse
    {
        return response()->json([
            'data' => $this->mine()->with('myPokemons')->findOrFail($battleTeam),
        ]);
    }

    public function update(Request $request, int $battleTeam): JsonResponse
    {
        $team = $this->mine()->findOrFail($battleTeam);
        $team->update($this->validated($request, $team->id));

        return response()->json(['data' => $team]);
    }

    public function destroy(int $battleTeam): JsonResponse
    {
        $this->mine()->findOrFail($battleTeam)->delete();

        return response()->json(['message' => 'Tim berhasil dihapus.']);
    }

    // Isolasi data: hanya milik user yang login
    private function mine(): Builder
    {
        return BattleTeam::where('user_id', auth()->id());
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('battle_teams', 'name')
                    ->where('user_id', auth()->id())
                    ->ignore($ignoreId),
            ],
        ], ['name.unique' => 'Nama tim sudah dipakai.']);
    }
}
