<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\Pokemon;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * FavoriteService
 *
 * Servicio para gestionar pokémon favoritos de usuarios
 * - Inserta/actualiza pokémon desde PokeAPI en BD
 * - Guarda/elimina favoritos de la base de datos
 * - Vincula favoritos al usuario autenticado
 * - Cachea favoritos para mejor performance
 * - Maneja excepciones y errores gracefully
 *
 * @package App\Services
 */
class FavoriteService
{
    /**
     * URL base de PokeAPI v2
     */
    private const POKEAPI_BASE = 'https://pokeapi.co/api/v2';

    /**
     * Timeout para requests a PokeAPI en segundos
     */
    private const API_TIMEOUT = 10;

    /**
     * TTL de caché de favoritos en segundos (1 hora)
     */
    private const FAVORITES_CACHE_TTL = 3600;

    /**
     * Agrega un pokémon a favoritos del usuario
     *
     * Estrategia:
     * 1. Obtiene datos del Pokémon de PokeAPI
     * 2. Inserta/actualiza el Pokémon en BD (si no existe)
     * 3. Crea el registro de favorito
     * 4. Invalida caché de favoritos
     *
     * @param User $user Usuario autenticado
     * @param int $pokemonId ID del pokémon (1-150)
     * @return Favorite
     * @throws Exception
     */
    public function addToFavorites(User $user, int $pokemonId): Favorite
    {
        try {
            // 1. Obtener datos de PokeAPI
            $pokemonData = $this->validatePokemonExists($pokemonId);

            // 2. Insertar/actualizar Pokémon en BD
            $pokemon = Pokemon::updateOrCreate(
                ['pokedex_id' => $pokemonId],
                [
                    'name' => $pokemonData['name'],
                    'type' => implode(',', $pokemonData['types']),
                    'image_url' => $pokemonData['image_url'] ?? null,
                    'description' => $pokemonData['description'] ?? null,
                    'hp' => $pokemonData['stats']['hp'] ?? null,
                    'attack' => $pokemonData['stats']['attack'] ?? null,
                    'defense' => $pokemonData['stats']['defense'] ?? null,
                    'sp_attack' => $pokemonData['stats']['sp_attack'] ?? null,
                    'sp_defense' => $pokemonData['stats']['sp_defense'] ?? null,
                    'speed' => $pokemonData['stats']['speed'] ?? null,
                ]
            );

            Log::info('Pokemon inserted/updated in BD', [
                'pokemon_id' => $pokemonId,
                'pokemon_name' => $pokemon->name,
            ]);

            // 3. Validar que no esté ya en favoritos
            $existing = Favorite::where('user_id', $user->id)
                ->where('pokemon_id', $pokemon->id)
                ->first();

            if ($existing) {
                throw new Exception("Pokemon already in favorites", 409);
            }

            // 4. Crear el favorito
            $favorite = Favorite::create([
                'user_id' => $user->id,
                'pokemon_id' => $pokemon->id,
                'pokemon_name' => $pokemon->name,
                'pokemon_type' => $pokemon->type,
            ]);

            // 5. Invalidar caché de favoritos del usuario
            Cache::forget("user_favorites:{$user->id}");

            Log::info('Favorite added and cache invalidated', [
                'user_id' => $user->id,
                'pokemon_id' => $pokemonId,
                'favorite_id' => $favorite->id,
            ]);

            return $favorite;
        } catch (Exception $e) {
            Log::error('Error adding favorite', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'pokemon_id' => $pokemonId,
            ]);
            throw $e;
        }
    }

    /**
     * Elimina un pokémon de favoritos del usuario
     * Invalida caché después de eliminar
     *
     * @param User $user Usuario autenticado
     * @param int $pokemonId ID del pokémon (pokedex_id)
     * @return bool
     * @throws Exception
     */
    public function removeFromFavorites(User $user, int $pokemonId): bool
    {
        try {
            // 1. Buscar el Pokemon en BD por pokedex_id
            $pokemon = Pokemon::where('pokedex_id', $pokemonId)->first();
            if (!$pokemon) {
                throw new Exception("Pokemon not found", 404);
            }

            // 2. Buscar el Favorite por user_id y pokemon_id (FK a pokemon.id)
            $favorite = Favorite::where('user_id', $user->id)
                ->where('pokemon_id', $pokemon->id)
                ->first();

            if (!$favorite) {
                throw new Exception("Favorite not found", 404);
            }

            $deleted = $favorite->delete();

            // 3. Invalidar caché de favoritos del usuario
            Cache::forget("user_favorites:{$user->id}");

            Log::info('Favorite removed and cache invalidated', [
                'user_id' => $user->id,
                'pokemon_id' => $pokemonId,
                'favorite_id' => $favorite->id,
            ]);

            return $deleted;
        } catch (Exception $e) {
            Log::error('Error removing favorite', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'pokemon_id' => $pokemonId,
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene todos los favoritos del usuario con caché
     *
     * Estrategia:
     * 1. Intenta obtener del caché (1 hora)
     * 2. Si no está en caché, consulta BD con JOIN a Pokemon
     * 3. Retorna datos actualizados del Pokemon
     * 4. Guarda en caché para futuras consultas
     *
     * @param User $user Usuario autenticado
     * @return Collection
     */
    public function getFavorites(User $user)
    {
        try {
            $cacheKey = "user_favorites:{$user->id}";

            // Intentar obtener del caché
            if (Cache::has($cacheKey)) {
                Log::debug('Favorites retrieved from cache', ['user_id' => $user->id]);
                $cached = Cache::get($cacheKey);
                // Convertir array de caché de vuelta a colección
                if (is_array($cached)) {
                    return collect($cached);
                }
                return $cached;
            }

            // Obtener de BD con eager loading de Pokemon
            $favorites = Favorite::where('user_id', $user->id)
                ->with('pokemon')  // Eager load el pokémon relacionado
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($favorite) {
                    // Retornar datos actualizados del Pokemon
                    return [
                        'id' => $favorite->id,
                        'user_id' => $favorite->user_id,
                        'pokemon_id' => $favorite->pokemon_id,
                        'pokemon_name' => $favorite->pokemon->name ?? $favorite->pokemon_name,
                        'pokemon_type' => $favorite->pokemon->type ?? $favorite->pokemon_type,
                        'pokedex_id' => $favorite->pokemon->pokedex_id ?? null,
                        'image_url' => $favorite->pokemon->image_url ?? null,
                        'description' => $favorite->pokemon->description ?? null,
                        'hp' => $favorite->pokemon->hp ?? null,
                        'attack' => $favorite->pokemon->attack ?? null,
                        'defense' => $favorite->pokemon->defense ?? null,
                        'sp_attack' => $favorite->pokemon->sp_attack ?? null,
                        'sp_defense' => $favorite->pokemon->sp_defense ?? null,
                        'speed' => $favorite->pokemon->speed ?? null,
                        'created_at' => $favorite->created_at,
                        'updated_at' => $favorite->updated_at,
                    ];
                });

            // Guardar en caché por 1 hora
            Cache::put($cacheKey, $favorites->toArray(), self::FAVORITES_CACHE_TTL);

            Log::debug('Favorites retrieved from DB and cached', [
                'user_id' => $user->id,
                'count' => $favorites->count(),
            ]);

            return $favorites;
        } catch (Exception $e) {
            Log::error('Error retrieving favorites', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            throw $e;
        }
    }

    /**
     * Comprueba si un pokémon está en favoritos del usuario
     *
     * @param User $user Usuario autenticado
     * @param int $pokemonId ID del pokémon
     * @return bool
     */
    public function isFavorite(User $user, int $pokemonId): bool
    {
        return Favorite::where('user_id', $user->id)
            ->where('pokemon_id', $pokemonId)
            ->exists();
    }

    /**
     * Valida que un pokémon exista en PokeAPI y retorna datos completos
     *
     * @param int $pokemonId ID del pokémon
     * @return array Datos del pokémon {id, name, types, image_url, stats, description}
     * @throws Exception
     */
    private function validatePokemonExists(int $pokemonId): array
    {
        try {
            // Validar rango
            if ($pokemonId < 1 || $pokemonId > 150) {
                throw new Exception("Invalid pokemon ID. Must be between 1 and 150", 400);
            }

            // Primero intentar obtener de BD
            $pokemon = Pokemon::where('pokedex_id', $pokemonId)->first();
            if ($pokemon) {
                Log::debug('Pokemon found in BD', ['pokedex_id' => $pokemonId, 'id' => $pokemon->id]);
                return [
                    'id' => $pokemon->id,
                    'name' => $pokemon->name,
                    'types' => array_map('strtolower', explode(',', $pokemon->type ?? 'normal')),
                    'image_url' => $pokemon->image_url,
                    'description' => $pokemon->description,
                    'stats' => [
                        'hp' => $pokemon->hp ?? 0,
                        'attack' => $pokemon->attack ?? 0,
                        'defense' => $pokemon->defense ?? 0,
                        'sp_attack' => $pokemon->sp_attack ?? 0,
                        'sp_defense' => $pokemon->sp_defense ?? 0,
                        'speed' => $pokemon->speed ?? 0,
                    ],
                ];
            }

            Log::debug('Pokemon not found in BD, calling PokeAPI', ['pokedex_id' => $pokemonId]);

            // Si no existe en BD, hacer request a PokeAPI
            $response = Http::timeout(self::API_TIMEOUT)
                ->get(self::POKEAPI_BASE . "/pokemon/{$pokemonId}")
                ->throw();

            $data = $response->json();

            // Extraer tipos
            $types = array_map(
                fn($type) => strtolower($type['type']['name']),
                $data['types'] ?? []
            );

            // Extraer stats
            $stats = [];
            foreach ($data['stats'] ?? [] as $stat) {
                $stat_name = strtolower(str_replace([' ', '-'], '_', $stat['stat']['name']));
                $stats[$stat_name] = $stat['base_stat'] ?? 0;
            }

            // Obtener image URL
            $imageUrl = $data['sprites']['front_default'] ??
                        $data['sprites']['other']['official-artwork']['front_default'] ??
                        null;

            return [
                'id' => $data['id'],
                'name' => ucfirst($data['name']),
                'types' => $types,
                'image_url' => $imageUrl,
                'description' => "Gen 1 Pokémon #{$pokemonId}",
                'stats' => [
                    'hp' => $stats['hp'] ?? 0,
                    'attack' => $stats['attack'] ?? 0,
                    'defense' => $stats['defense'] ?? 0,
                    'sp_attack' => $stats['sp_atk'] ?? 0,
                    'sp_defense' => $stats['sp_def'] ?? 0,
                    'speed' => $stats['speed'] ?? 0,
                ],
            ];
        } catch (Exception $e) {
            if ($e->getCode() === 404 || $e->getCode() === 400) {
                throw $e;
            }

            Log::error('Error validating pokemon in PokeAPI', [
                'error' => $e->getMessage(),
                'pokemon_id' => $pokemonId,
            ]);

            throw new Exception('Failed to validate pokemon from PokeAPI', 503, $e);
        }
    }
}
