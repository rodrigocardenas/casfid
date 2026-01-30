<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * PokemonService
 * 
 * Servicio para consumir PokeAPI v2 y gestionar caché de pokémon
 * - Obtiene primeros 150 pokémon (Generación 1)
 * - Filtra por nombre, tipo, favoritos
 * - Cachea respuestas en Redis por 24 horas
 * - Manejo de errores graceful si PokeAPI falla
 * 
 * @package App\Services
 */
class PokemonService
{
    /**
     * URL base de PokeAPI v2
     */
    private const POKEAPI_BASE = 'https://pokeapi.co/api/v2';

    /**
     * Cantidad de pokémon de generación 1
     */
    private const FIRST_GENERATION_COUNT = 151; // 1-150 más el 0

    /**
     * TTL de caché en segundos (24 horas)
     */
    private const CACHE_TTL = 86400;

    /**
     * Timeout para requests a PokeAPI en segundos
     */
    private const API_TIMEOUT = 10;

    /**
     * Obtiene lista paginada de pokémon de generación 1
     * 
     * @param int $page
     * @param int $perPage
     * @param string|null $type Filtrar por tipo (water, fire, grass, etc)
     * @param string|null $search Buscar por nombre
     * @return array
     * @throws Exception
     */
    public function getPokemonList(
        int $page = 1,
        int $perPage = 20,
        ?string $type = null,
        ?string $search = null
    ): array {
        try {
            // Obtener todo el listado de generación 1
            $allPokemon = $this->fetchGeneration1Pokemon();

            // Aplicar filtros
            if ($search) {
                $allPokemon = array_filter($allPokemon, function ($pokemon) use ($search) {
                    return stripos($pokemon['name'], $search) !== false;
                });
            }

            if ($type) {
                $allPokemon = array_filter($allPokemon, function ($pokemon) use ($type) {
                    return in_array(strtolower($type), $pokemon['types']);
                });
            }

            // Resetear índices después de filtros
            $allPokemon = array_values($allPokemon);

            // Calcular paginación
            $total = count($allPokemon);
            $totalPages = ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;

            // Validar página
            if ($page < 1 || $page > $totalPages) {
                throw new Exception("Page $page not found. Total pages: $totalPages", 404);
            }

            // Obtener página actual
            $paginated = array_slice($allPokemon, $offset, $perPage);

            return [
                'data' => $paginated,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => $totalPages,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1,
                ],
            ];
        } catch (Exception $e) {
            Log::error('Error fetching pokemon list', [
                'error' => $e->getMessage(),
                'page' => $page,
                'search' => $search,
                'type' => $type,
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene detalles completos de un pokémon por ID
     * 
     * @param int $pokemonId ID del pokémon (1-150)
     * @return array
     * @throws Exception
     */
    public function getPokemonDetail(int $pokemonId): array
    {
        try {
            // Validar que esté en generación 1
            if ($pokemonId < 1 || $pokemonId > 150) {
                throw new Exception("Pokemon with ID $pokemonId not found in Generation 1", 404);
            }

            $cacheKey = "pokemon:detail:{$pokemonId}";

            // Intentar obtener del caché
            if (Cache::has($cacheKey)) {
                Log::debug("Pokemon detail from cache", ['id' => $pokemonId]);
                return Cache::get($cacheKey);
            }

            // Obtener de PokeAPI
            $pokemon = $this->fetchPokemonFromApi($pokemonId);

            // Guardar en caché
            Cache::put($cacheKey, $pokemon, self::CACHE_TTL);

            Log::info("Pokemon detail fetched from API", ['id' => $pokemonId]);
            return $pokemon;
        } catch (Exception $e) {
            Log::error('Error fetching pokemon detail', [
                'error' => $e->getMessage(),
                'pokemon_id' => $pokemonId,
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene todos los pokémon de generación 1 con caché
     * 
     * @return array
     * @throws Exception
     */
    private function fetchGeneration1Pokemon(): array
    {
        $cacheKey = 'pokemon:generation:1';

        // Intentar obtener del caché
        if (Cache::has($cacheKey)) {
            Log::debug('Generation 1 pokemon from cache');
            return Cache::get($cacheKey);
        }

        // Obtener de PokeAPI
        $pokemon = $this->fetchAllPokemonFromApi();

        // Guardar en caché
        Cache::put($cacheKey, $pokemon, self::CACHE_TTL);

        Log::info('Generation 1 pokemon fetched from API', ['count' => count($pokemon)]);
        return $pokemon;
    }

    /**
     * Obtiene lista de todos los pokémon de PokeAPI
     * 
     * @return array
     * @throws Exception
     */
    private function fetchAllPokemonFromApi(): array
    {
        try {
            $pokemon = [];
            $offset = 0;
            $limit = 50;

            // Fetchear en bloques de 50
            while (count($pokemon) < self::FIRST_GENERATION_COUNT) {
                $response = Http::timeout(self::API_TIMEOUT)
                    ->get("{$this->POKEAPI_BASE}/pokemon", [
                        'offset' => $offset,
                        'limit' => $limit,
                    ])
                    ->throw();

                $results = $response->json('results', []);

                if (empty($results)) {
                    break;
                }

                foreach ($results as $result) {
                    // Solo primeros 150 pokémon
                    if (count($pokemon) >= self::FIRST_GENERATION_COUNT) {
                        break;
                    }

                    $id = $this->extractIdFromUrl($result['url']);
                    $pokemon[] = $this->normalizePokemon(
                        $result['name'],
                        $id,
                        null // types serán cargados después
                    );
                }

                $offset += $limit;
            }

            // Cargar tipos para cada pokémon
            $pokemon = $this->loadPokemonTypes($pokemon);

            return $pokemon;
        } catch (Exception $e) {
            Log::error('Error fetching all pokemon from API', ['error' => $e->getMessage()]);
            throw new Exception('Failed to fetch pokemon from PokeAPI', 503, $e);
        }
    }

    /**
     * Obtiene detalles de un pokémon específico de PokeAPI
     * 
     * @param int $pokemonId
     * @return array
     * @throws Exception
     */
    private function fetchPokemonFromApi(int $pokemonId): array
    {
        try {
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
                'image' => $data['sprites']['other']['official-artwork']['front_default'] 
                    ?? $data['sprites']['front_default']
                    ?? null,
                'types' => $types,
                'height' => $data['height'] / 10, // Convertir a metros
                'weight' => $data['weight'] / 10, // Convertir a kg
                'base_experience' => $data['base_experience'],
                'abilities' => array_map(
                    fn($ability) => ucfirst(str_replace('-', ' ', $ability['ability']['name'])),
                    $data['abilities'] ?? []
                ),
                'stats' => $this->normalizePokemonStats($data['stats'] ?? []),
            ];
        } catch (Exception $e) {
            if ($e->getCode() === 404) {
                throw new Exception("Pokemon not found", 404);
            }
            Log::error('Error fetching pokemon detail from API', [
                'error' => $e->getMessage(),
                'pokemon_id' => $pokemonId,
            ]);
            throw new Exception('Failed to fetch pokemon from PokeAPI', 503, $e);
        }
    }

    /**
     * Carga tipos para múltiples pokémon
     * 
     * @param array $pokemon
     * @return array
     */
    private function loadPokemonTypes(array $pokemon): array
    {
        return array_map(function ($poke) {
            try {
                $response = Http::timeout(self::API_TIMEOUT)
                    ->get("{$this->POKEAPI_BASE}/pokemon/{$poke['id']}")
                    ->throw();

                $data = $response->json();
                $types = array_map(
                    fn($type) => strtolower($type['type']['name']),
                    $data['types'] ?? []
                );

                return array_merge($poke, ['types' => $types]);
            } catch (Exception $e) {
                Log::warning("Failed to load types for pokemon {$poke['id']}");
                return array_merge($poke, ['types' => []]);
            }
        }, $pokemon);
    }

    /**
     * Normaliza datos de un pokémon
     * 
     * @param string $name
     * @param int $id
     * @param array|null $types
     * @return array
     */
    private function normalizePokemon(string $name, int $id, ?array $types = null): array
    {
        return [
            'id' => $id,
            'name' => ucfirst($name),
            'image' => $this->getPokemonImageUrl($id),
            'types' => $types ?? [],
        ];
    }

    /**
     * Obtiene URL de imagen oficial de pokémon
     * 
     * @param int $id
     * @return string
     */
    private function getPokemonImageUrl(int $id): string
    {
        return "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/{$id}.png";
    }

    /**
     * Extrae ID desde URL de PokeAPI
     * 
     * @param string $url
     * @return int
     */
    private function extractIdFromUrl(string $url): int
    {
        preg_match('/\/(\d+)\/$/', $url, $matches);
        return (int) ($matches[1] ?? 0);
    }

    /**
     * Normaliza stats de pokémon
     * 
     * @param array $stats
     * @return array
     */
    private function normalizePokemonStats(array $stats): array
    {
        $statMap = [
            'hp' => 'HP',
            'attack' => 'Attack',
            'defense' => 'Defense',
            'special-attack' => 'Sp. Attack',
            'special-defense' => 'Sp. Defense',
            'speed' => 'Speed',
        ];

        $result = [];
        foreach ($stats as $stat) {
            $statName = $stat['stat']['name'] ?? '';
            $displayName = $statMap[$statName] ?? ucfirst(str_replace('-', ' ', $statName));
            $result[$displayName] = $stat['base_stat'] ?? 0;
        }

        return $result;
    }
}
