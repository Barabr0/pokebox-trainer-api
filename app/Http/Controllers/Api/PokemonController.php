<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PokeApiService;
use Illuminate\Http\JsonResponse;

class PokemonController extends Controller
{
    public function __construct(private PokeApiService $pokeApi) {}

    // GET /api/pokemon (List Page)
    public function index(): JsonResponse
    {
        $types = $this->pokeApi->types();

        $data = collect($this->pokeApi->list())
            ->map(fn (array $p) => [
                'nomor_urut' => $p['nomor_urut'],
                'name' => $p['name'],
                'gambar_sprite' => $this->pokeApi->spriteUrl($p['nomor_urut']),
                'tipe' => $types[$p['nomor_urut']],
            ]);

        return response()->json(['data' => $data]);
    }

    // GET /api/pokemon/{nomorUrut} (Detail Page)
    public function show(int $nomorUrut): JsonResponse
    {
        $pokemon = $this->pokeApi->find($nomorUrut);

        if (! $pokemon) {
            return response()->json(['message' => 'Pokemon tidak ditemukan.'], 404);
        }

        return response()->json(['data' => [
            'nomor_urut' => $pokemon['nomor_urut'],
            'name' => $pokemon['name'],
            'gambar_sprite' => $this->pokeApi->spriteUrl($pokemon['nomor_urut']),
            'url_detail' => $pokemon['url'],
            'tipe' => $this->pokeApi->types()[$nomorUrut],
        ]]);
    }
}
