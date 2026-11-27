<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PokeApiService
{
    private const BASE_URL = 'https://pokeapi.co/api/v2/pokemon';

    private const SPRITE_URL = 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon';

    /** Data List Page: nomor urut dan name (disimpan di cache 1 jam). */
    public function list(): array
    {
        return Cache::remember('pokeapi.list', now()->addHour(), function () {
            $results = Http::acceptJson()
                ->timeout(10)
                ->get(self::BASE_URL, ['limit' => 50])
                ->throw()
                ->json('results');

            return collect($results)->map(fn (array $item) => [
                'nomor_urut' => $this->idFromUrl($item['url']),
                'name' => $item['name'],
                'url' => $item['url'],
            ])->all();
        });
    }

    /** Tipe tiap Pokemon, diambil dari API detail (disimpan di cache 1 hari). */
    public function types(): array
    {
        return Cache::remember('pokeapi.types', now()->addDay(), function () {
            $types = [];

            // 10 request sekaligus agar lebih cepat
            foreach (array_chunk($this->list(), 10) as $chunk) {
                $responses = Http::pool(fn (Pool $pool) => array_map(
                    fn (array $p) => $pool->as((string) $p['nomor_urut'])
                        ->acceptJson()
                        ->timeout(10)
                        ->get($p['url']),
                    $chunk
                ));

                foreach ($chunk as $p) {
                    $response = $responses[(string) $p['nomor_urut']];

                    // Jika ada yang gagal, hasil tidak disimpan ke cache
                    if (! $response instanceof Response || ! $response->successful()) {
                        throw new RuntimeException('Gagal mengambil tipe dari PokeAPI.');
                    }

                    $types[$p['nomor_urut']] = collect($response->json('types'))
                        ->sortBy('slot')
                        ->pluck('type.name')
                        ->values()
                        ->all();
                }
            }

            return $types;
        });
    }

    /** Cari satu Pokemon di dalam daftar 50 berdasarkan nomor urut. */
    public function find(int $nomorUrut): ?array
    {
        return collect($this->list())->firstWhere('nomor_urut', $nomorUrut);
    }

    public function spriteUrl(int $id): string
    {
        return self::SPRITE_URL."/{$id}.png";
    }

    // https://pokeapi.co/api/v2/pokemon/25/ => 25
    private function idFromUrl(string $url): int
    {
        return (int) basename(rtrim($url, '/'));
    }
}
