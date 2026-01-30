<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PokemonController;
use App\Http\Controllers\FavoriteController;
use App\Http\Middleware\AuthRateLimiter;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes v1
|--------------------------------------------------------------------------
|
| Este archivo contiene todas las rutas de la API v1 del BFF.
| Está protegido bajo el middleware 'api' y tiene el prefijo 'v1'
|
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Rutas de Autenticación (Public con Rate Limiting)
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->middleware(AuthRateLimiter::class)->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    });

    /*
    |--------------------------------------------------------------------------
    | Rutas Públicas (Sin autenticación)
    |--------------------------------------------------------------------------
    */

    // Pokemon - Rutas especiales PRIMERO, luego paramétrica, luego genérica
    Route::get('/pokemon/filters', [PokemonController::class, 'filters'])->name('pokemon.filters');
    Route::get('/pokemon/types', [PokemonController::class, 'types'])->name('pokemon.types');
    Route::get('/pokemon/{id}', [PokemonController::class, 'show'])->where('id', '[0-9]+')->name('pokemon.show');
    Route::get('/pokemon', [PokemonController::class, 'index'])->name('pokemon.index');

    /*
    |--------------------------------------------------------------------------
    | Rutas Protegidas por token
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth.token')->group(function () {

        // Autenticación
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
            Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
        });

        // Favoritos
        Route::post('/favorites', [FavoriteController::class, 'store'])->name('favorites.store');
        Route::delete('/favorites/{pokemon_id}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    });
});

/*
|--------------------------------------------------------------------------
| Health Check (Sin autenticación)
|--------------------------------------------------------------------------
*/
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
    ]);
});
