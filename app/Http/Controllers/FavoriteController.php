<?php

namespace App\Http\Controllers;

use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * FavoriteController
 * 
 * Endpoints para gestionar favoritos de pokémon:
 * - POST /api/v1/favorites (agregar)
 * - DELETE /api/v1/favorites/{pokemon_id} (eliminar)
 * - GET /api/v1/favorites (listar)
 * 
 * Todos los endpoints requieren autenticación JWT
 * 
 * @package App\Http\Controllers
 */
class FavoriteController extends Controller
{
    /**
     * Inyectar FavoriteService
     * 
     * @param FavoriteService $favoriteService
     */
    public function __construct(
        private FavoriteService $favoriteService
    ) {
    }

    /**
     * Agrega un pokémon a favoritos del usuario autenticado
     * 
     * Request Body:
     * {
     *   "pokemon_id": 1
     * }
     * 
     * Response 201 Created:
     * {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "pokemon_id": 1,
     *     "pokemon_name": "Bulbasaur",
     *     "pokemon_type": "grass,poison",
     *     "created_at": "2026-01-30T16:29:00Z",
     *     "updated_at": "2026-01-30T16:29:00Z"
     *   },
     *   "message": "Pokemon added to favorites",
     *   "timestamp": "2026-01-30T16:29:00Z"
     * }
     * 
     * Response 409 Conflict (ya está en favoritos):
     * {
     *   "success": false,
     *   "error": "Pokemon already in favorites",
     *   "timestamp": "2026-01-30T16:29:00Z"
     * }
     * 
     * Response 400 Bad Request (ID inválido):
     * {
     *   "success": false,
     *   "error": "Invalid pokemon ID. Must be between 1 and 150",
     *   "timestamp": "2026-01-30T16:29:00Z"
     * }
     * 
     * Response 503 Service Unavailable (PokeAPI falla):
     * {
     *   "success": false,
     *   "error": "Failed to validate pokemon from PokeAPI",
     *   "timestamp": "2026-01-30T16:29:00Z"
     * }
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validar input
            $validated = $request->validate([
                'pokemon_id' => 'required|integer|min:1|max:150',
            ], [
                'pokemon_id.required' => 'El ID del pokémon es requerido',
                'pokemon_id.integer' => 'El ID del pokémon debe ser un número entero',
                'pokemon_id.min' => 'El ID del pokémon debe ser mayor a 0',
                'pokemon_id.max' => 'El ID del pokémon debe ser menor a 150',
            ]);

            // Obtener usuario autenticado
            $user = Auth::user();

            // Agregar a favoritos
            $favorite = $this->favoriteService->addToFavorites(
                $user,
                $validated['pokemon_id']
            );

            return response()->json([
                'success' => true,
                'data' => $favorite,
                'message' => 'Pokemon added to favorites',
                'timestamp' => now()->toIso8601String(),
            ], 201);
        } catch (Exception $e) {
            // Errores conocidos (400, 409)
            if (in_array($e->getCode(), [400, 409])) {
                Log::warning('Favorite store error', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String(),
                ], $e->getCode() ?: 400);
            }

            // Errores de validación (422)
            if ($e->getCode() === 422) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'message' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String(),
                ], 422);
            }

            // Errores de PokeAPI (503)
            Log::error('Favorite service error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], 503);
        }
    }

    /**
     * Elimina un pokémon de favoritos del usuario autenticado
     * 
     * URL Parameters:
     * - pokemon_id (int): ID del pokémon
     * 
     * Response 200 OK:
     * {
     *   "success": true,
     *   "message": "Pokemon removed from favorites",
     *   "timestamp": "2026-01-30T16:29:00Z"
     * }
     * 
     * Response 404 Not Found:
     * {
     *   "success": false,
     *   "error": "Favorite not found",
     *   "timestamp": "2026-01-30T16:29:00Z"
     * }
     * 
     * @param int $pokemonId
     * @return JsonResponse
     */
    public function destroy(int $pokemonId): JsonResponse
    {
        try {
            // Validar ID
            if ($pokemonId < 1 || $pokemonId > 150) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid pokemon ID. Must be between 1 and 150',
                    'timestamp' => now()->toIso8601String(),
                ], 400);
            }

            // Obtener usuario autenticado
            $user = Auth::user();

            // Eliminar de favoritos
            $this->favoriteService->removeFromFavorites($user, $pokemonId);

            return response()->json([
                'success' => true,
                'message' => 'Pokemon removed from favorites',
                'timestamp' => now()->toIso8601String(),
            ], 200);
        } catch (Exception $e) {
            // Errores conocidos (404)
            if ($e->getCode() === 404) {
                Log::warning('Favorite destroy not found', ['pokemon_id' => $pokemonId]);

                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            // Otros errores
            Log::error('Favorite destroy error', [
                'error' => $e->getMessage(),
                'pokemon_id' => $pokemonId,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to remove favorite',
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Obtiene lista de favoritos del usuario autenticado
     * 
     * Query Parameters:
     * - page (int): Número de página (default 1)
     * - per_page (int): Items por página (default 20, max 50)
     * 
     * Response 200 OK:
     * {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "user_id": 1,
     *       "pokemon_id": 1,
     *       "pokemon_name": "Bulbasaur",
     *       "pokemon_type": "grass,poison",
     *       "created_at": "2026-01-30T16:29:00Z",
     *       "updated_at": "2026-01-30T16:29:00Z"
     *     },
     *     ...
     *   ],
     *   "pagination": {
     *     "current_page": 1,
     *     "per_page": 20,
     *     "total": 15,
     *     "total_pages": 1
     *   },
     *   "timestamp": "2026-01-30T16:29:00Z"
     * }
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Validar parámetros
            $validated = $request->validate([
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:50',
            ]);

            // Obtener usuario autenticado
            $user = Auth::user();

            // Obtener favoritos
            $favorites = $this->favoriteService->getFavorites($user);

            // Paginar
            $page = $validated['page'] ?? 1;
            $perPage = $validated['per_page'] ?? 20;
            $total = $favorites->count();
            $totalPages = ceil($total / $perPage);

            // Validar página
            if ($page < 1 || ($total > 0 && $page > $totalPages)) {
                throw new Exception("Page $page not found. Total pages: $totalPages", 404);
            }

            // Aplicar paginación
            $offset = ($page - 1) * $perPage;
            $paginated = $favorites->slice($offset, $perPage);

            return response()->json([
                'success' => true,
                'data' => $paginated,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => $totalPages,
                ],
                'timestamp' => now()->toIso8601String(),
            ], 200);
        } catch (Exception $e) {
            if ($e->getCode() === 404) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            Log::error('Favorite index error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve favorites',
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }
}
