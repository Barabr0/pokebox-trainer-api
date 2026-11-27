<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MyPokemon;
use App\Services\PokeApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MyPokemonController extends Controller
{
    public function __construct(private PokeApiService $pokeApi) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => MyPokemon::with('battleTeam')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $myPokemon = MyPokemon::create($data);

        return response()->json(['data' => $myPokemon->load('battleTeam')], 201);
    }

    public function show(MyPokemon $myPokemon): JsonResponse
    {
        return response()->json(['data' => $myPokemon->load('battleTeam')]);
    }

    public function update(Request $request, MyPokemon $myPokemon): JsonResponse
    {
        $myPokemon->update($this->validated($request, $myPokemon));

        return response()->json(['data' => $myPokemon->load('battleTeam')]);
    }

    public function destroy(MyPokemon $myPokemon): JsonResponse
    {
        $myPokemon->delete();

        return response()->json(['message' => 'Pokemon berhasil dihapus dari tim.']);
    }

    /**
     * Validasi input. nama_pokemon diambil otomatis dari PokeAPI.
     * Saat update, field yang tidak dikirim memakai nilai lama.
     */
    private function validated(Request $request, ?MyPokemon $current = null): array
    {
        if ($current) {
            $request->mergeIfMissing($current->only(['battle_team_id', 'nomor_urut', 'peran_tim']));
        }

        $data = $request->validate([
            'battle_team_id' => ['required', 'integer', 'exists:battle_teams,id'],
            'nomor_urut' => [
                'required', 'integer', 'min:1',
                Rule::unique('my_pokemons', 'nomor_urut')
                    ->where('battle_team_id', $request->input('battle_team_id'))
                    ->ignore($current?->id),
            ],
            'peran_tim' => ['required', 'string', 'max:50'],
        ], [
            'nomor_urut.unique' => 'Pokemon ini sudah ada di tim tersebut.',
        ]);

        $pokemon = $this->pokeApi->find((int) $data['nomor_urut']);

        if (! $pokemon) {
            throw ValidationException::withMessages([
                'nomor_urut' => 'Nomor urut tidak ada di daftar PokeAPI.',
            ]);
        }

        $data['nama_pokemon'] = $pokemon['name'];

        return $data;
    }
}
