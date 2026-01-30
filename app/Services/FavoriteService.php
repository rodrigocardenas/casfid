<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * FavoriteService
 * 
 * Servicio para gestionar pokémon favoritos de usuarios
 * - Valida que el pokémon exista en PokeAPI
 * - Guarda/elimina favoritos de la base de datos
 * - Vincula favoritos al usuario autenticado
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
     * Agrega un pokémon a favoritos del usuario
     * 
     * @param User $user Usuario autenticado
     * @param int $pokemonId ID del pokémon (1-150)
     * @return Favorite
     * @throws Exception
     */
    public function addToFavorites(User $user, int $pokemonId): Favorite
    {
        try {
            // Validar que el pokémon existe en PokeAPI
            $pokemonData = $this->validatePokemonExists($pokemonId);

            // Validar que no esté ya en favoritos
            $existing = Favorite::where('user_id', $user->id)
                ->where('pokemon_id', $pokemonId)
                ->first();

            if ($existing) {
                throw new Exception("Pokemon already in favorites", 409);
            }

            // Crear el favorito
            $favorite = Favorite::create([
                'user_id' => $user->id,
                'pokemon_id' => $pokemonId,
                'pokemon_name' => $pokemonData['name'],
                'pokemon_type' => implode(',', $pokemonData['types']),
            ]);

            Log::info('Favorite added', [
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
     * 
     * @param User $user Usuario autenticado
     * @param int $pokemonId ID del pokémon
     * @return bool
     * @throws Exception
     */
    public function removeFromFavorites(User $user, int $pokemonId): bool
    {
        try {
            $favorite = Favorite::where('user_id', $user->id)
                ->where('pokemon_id', $pokemonId)
                ->first();

            if (!$favorite) {
                throw new Exception("Favorite not found", 404);
            }

            $deleted = $favorite->delete();

            Log::info('Favorite removed', [
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
     * Obtiene todos los favoritos del usuario
     * 
     * @param User $user Usuario autenticado
     * @return Collection
     */
    public function getFavorites(User $user): Collection
    {
        try {
            $favorites = Favorite::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            Log::debug('Favorites retrieved', [
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
     * Valida que un pokémon exista en PokeAPI
     * 
     * @param int $pokemonId ID del pokémon
     * @return array Datos del pokémon {id, name, types}
     * @throws Exception
     */
    private function validatePokemonExists(int $pokemonId): array
    {
        try {
            // Validar rango
            if ($pokemonId < 1 || $pokemonId > 150) {
                throw new Exception("Invalid pokemon ID. Must be between 1 and 150", 400);
            }

            // Hacer request a PokeAPI
            $response = Http::timeout(self::API_TIMEOUT)
                ->get("{$this->POKEAPI_BASE}/pokemon/{$pokemonId}")
                ->throw();

            $data = $response->json();

            // Extraer tipos
            $types = array_map(
                fn($type) => strtolower($type['type']['name']),
                $data['types'] ?? []
            );

            return [
                'id' => $data['id'],
                'name' => ucfirst($data['name']),
                'types' => $types,
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
