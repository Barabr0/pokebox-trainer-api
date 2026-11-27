<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MyPokemon;
use App\Services\PokeApiService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MyPokemonController extends Controller
{
    public function __construct(private PokeApiService $pokeApi) {}

    // Filter opsional: ?battle_team_id=1
    public function index(Request $request): JsonResponse
    {
        $items = $this->mine()
            ->with('battleTeam')
            ->when($request->filled('battle_team_id'), fn (Builder $q) => $q
                ->where('battle_team_id', $request->integer('battle_team_id')))
            ->latest('id')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['user_id'] = auth()->id();
        $item = MyPokemon::create($data);

        return response()->json(['data' => $item->load('battleTeam')], 201);
    }

    public function show(int $myPokemon): JsonResponse
    {
        return response()->json([
            'data' => $this->mine()->with('battleTeam')->findOrFail($myPokemon),
        ]);
    }

    public function update(Request $request, int $myPokemon): JsonResponse
    {
        $item = $this->mine()->findOrFail($myPokemon);
        $item->update($this->validated($request, $item));

        return response()->json(['data' => $item->load('battleTeam')]);
    }

    public function destroy(int $myPokemon): JsonResponse
    {
        $this->mine()->findOrFail($myPokemon)->delete();

        return response()->json(['message' => 'Pokemon berhasil dihapus.']);
    }

    private function mine(): Builder
    {
        return MyPokemon::where('user_id', auth()->id());
    }

    private function validated(Request $request, ?MyPokemon $current = null): array
    {
        if ($current) {
            $request->mergeIfMissing($current->only([
                'battle_team_id', 'title', 'notes', 'image_url', 'nomor_urut', 'peran_tim',
            ]));
        }

        $data = $request->validate([
            // Tim harus milik user yang login
            'battle_team_id' => [
                'required', 'integer',
                Rule::exists('battle_teams', 'id')->where('user_id', auth()->id()),
            ],
            'title' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url:http,https', 'max:500'],
            'nomor_urut' => [
                'required', 'integer', 'min:1',
                Rule::unique('my_pokemons', 'nomor_urut')
                    ->where('battle_team_id', $request->input('battle_team_id'))
                    ->ignore($current?->id),
            ],
            'peran_tim' => ['required', 'string', 'max:50'],
        ], ['nomor_urut.unique' => 'Pokemon ini sudah ada di tim tersebut.']);

        $pokemon = $this->pokeApi->find((int) $data['nomor_urut']);

        if (! $pokemon) {
            throw ValidationException::withMessages([
                'nomor_urut' => 'Nomor urut tidak ada di daftar PokeAPI.',
            ]);
        }

        $data['nama_pokemon'] = $pokemon['name'];
        // Judul kosong diisi nama Pokemon
        $data['title'] = ($data['title'] ?? null) ?: $pokemon['name'];

        return $data;
    }
}
