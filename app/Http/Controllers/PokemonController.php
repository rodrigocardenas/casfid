<?php

namespace App\Http\Controllers;

use App\Services\PokemonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * PokemonController
 *
 * Endpoints para el dominio de Pokémon:
 * - GET /api/v1/pokemon (listado paginado con filtros)
 * - GET /api/v1/pokemon/{id} (detalle de un pokémon)
 *
 * @package App\Http\Controllers
 */
class PokemonController extends Controller
{
    /**
     * Inyectar PokemonService
     *
     * @param PokemonService $pokemonService
     */
    public function __construct(
        private PokemonService $pokemonService
    ) {
    }

    /**
     * Obtiene lista paginada de pokémon de generación 1
     *
     * Query Parameters:
     * - page (int): Número de página (default 1)
     * - per_page (int): Items por página (default 20, max 50)
     * - type (string): Filtrar por tipo (water, fire, grass, etc)
     * - search (string): Buscar por nombre
     *
     * Response 200:
     * {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Bulbasaur",
     *       "image": "https://...",
     *       "types": ["grass", "poison"]
     *     },
     *     ...
     *   ],
     *   "pagination": {
     *     "current_page": 1,
     *     "per_page": 20,
     *     "total": 150,
     *     "total_pages": 8,
     *     "has_next": true,
     *     "has_prev": false
     *   },
     *   "timestamp": "2026-01-30T16:29:00Z"
     * }
     *
     * Response 503 (PokeAPI unavailable):
     * {
     *   "success": false,
     *   "error": "Pokémon service temporarily unavailable",
     *   "message": "Failed to fetch pokemon from PokeAPI",
     *   "timestamp": "2026-01-30T16:29:00Z"
     * }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Validar y obtener parámetros
            $validated = $request->validate([
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:50',
                'type' => 'string|max:20|nullable',
                'search' => 'string|max:100|nullable',
            ]);

            $page = $validated['page'] ?? 1;
            $perPage = $validated['per_page'] ?? 20;
            $type = $validated['type'] ?? null;
            $search = $validated['search'] ?? null;

            // Obtener pokémon
            $result = $this->pokemonService->getPokemonList(
                page: $page,
                perPage: $perPage,
                type: $type,
                search: $search
            );

            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'pagination' => $result['pagination'],
                'timestamp' => now()->toIso8601String(),
            ], 200);
        } catch (Exception $e) {
            // Errores conocidos (404, 400)
            if (in_array($e->getCode(), [400, 404])) {
                Log::warning('Pokemon listing error', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String(),
                ], $e->getCode() ?: 400);
            }

            // Errores de PokeAPI (503)
            Log::error('Pokemon service error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Pokémon service temporarily unavailable',
                'message' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], 503);
        }
    }

    /**
     * Obtiene detalles completos de un pokémon
     *
     * URL Parameters:
     * - id (int): ID del pokémon (1-150)
     *
     * Response 200:
     * {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "name": "Bulbasaur",
     *     "image": "https://...",
     *     "types": ["grass", "poison"],
     *     "height": 0.7,
     *     "weight": 6.9,
     *     "base_experience": 64,
     *     "abilities": ["Overgrow", "Chlorophyll"],
     *     "stats": {
     *       "HP": 45,
     *       "Attack": 49,
     *       "Defense": 49,
     *       "Sp. Attack": 65,
     *       "Sp. Defense": 65,
     *       "Speed": 45
     *     }
     *   },
     *   "timestamp": "2026-01-30T16:29:00Z"
     * }
     *
     * Response 404 (Pokemon not found):
     * {
     *   "success": false,
     *   "error": "Pokemon not found",
     *   "timestamp": "2026-01-30T16:29:00Z"
     * }
     *
     * Response 503 (PokeAPI unavailable):
     * {
     *   "success": false,
     *   "error": "Pokémon service temporarily unavailable",
     *   "message": "Failed to fetch pokemon from PokeAPI",
     *   "timestamp": "2026-01-30T16:29:00Z"
     * }
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            // Validar ID
            if ($id < 1 || $id > 150) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid pokemon ID. Must be between 1 and 150',
                    'timestamp' => now()->toIso8601String(),
                ], 400);
            }

            // Obtener pokémon
            $pokemon = $this->pokemonService->getPokemonDetail($id);

            return response()->json([
                'success' => true,
                'data' => $pokemon,
                'timestamp' => now()->toIso8601String(),
            ], 200);
        } catch (Exception $e) {
            // Errores conocidos (404)
            if ($e->getCode() === 404) {
                Log::warning('Pokemon detail not found', ['id' => $id]);

                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            // Errores de PokeAPI (503)
            Log::error('Pokemon service error', [
                'error' => $e->getMessage(),
                'pokemon_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Pokémon service temporarily unavailable',
                'message' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], 503);
        }
    }

    /**
     * Obtiene filtros disponibles (tipos de pokémon)
     *
     * Response 200:
     * {
     *   "success": true,
     *   "data": {
     *     "types": [
     *       "normal", "fighting", "flying", "poison", "ground",
     *       "rock", "bug", "ghost", "steel", "fire", "water",
     *       "grass", "electric", "psychic", "ice", "dragon",
     *       "dark", "fairy"
     *     ]
     *   },
     *   "timestamp": "2026-01-30T16:29:00Z"
     * }
     *
     * @return JsonResponse
     */
    public function filters(): JsonResponse
    {
        $types = [
            'normal', 'fighting', 'flying', 'poison', 'ground', 'rock',
            'bug', 'ghost', 'steel', 'fire', 'water', 'grass',
            'electric', 'psychic', 'ice', 'dragon', 'dark', 'fairy'
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'types' => $types,
            ],
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }
}
