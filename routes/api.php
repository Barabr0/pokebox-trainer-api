<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BattleTeamController;
use App\Http\Controllers\Api\MyPokemonController;
use App\Http\Controllers\Api\PokemonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Publik
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Wajib login
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);

    // PokeAPI
    Route::get('pokemon', [PokemonController::class, 'index']);
    Route::get('pokemon/{nomorUrut}', [PokemonController::class, 'show'])->whereNumber('nomorUrut');

    // Database lokal
    Route::apiResource('battle-teams', BattleTeamController::class);
    Route::apiResource('my-pokemons', MyPokemonController::class)
        ->parameters(['my-pokemons' => 'myPokemon']);
});
