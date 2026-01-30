<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PokemonController;
use App\Http\Middleware\AuthRateLimiter;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes v1
|--------------------------------------------------------------------------
|
| Este archivo contiene todas las rutas de la API v1 del BFF.
| Está protegido bajo el middleware 'api' y tiene el prefijo 'api/v1'
|
*/

Route::prefix('api/v1')->group(function () {
    
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
    
    // Pokemon - Listado (público para todos)
    Route::get('/pokemon', [PokemonController::class, 'index'])->name('pokemon.index');
    Route::get('/pokemon/{id}', [PokemonController::class, 'show'])->name('pokemon.show');
    Route::get('/pokemon/filters', [PokemonController::class, 'filters'])->name('pokemon.filters');

    /*
    |--------------------------------------------------------------------------
    | Rutas Protegidas por JWT
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:api')->group(function () {
        
        // Autenticación
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
            Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
        });

        // Favoritos (Por implementar en Prompt 3.3)
        // Route::post('/favorites', [FavoriteController::class, 'store'])->name('favorites.store');
        // Route::delete('/favorites/{pokemon_id}', [FavoriteController::class, 'destroy'])->name('favorites.destroy');
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
