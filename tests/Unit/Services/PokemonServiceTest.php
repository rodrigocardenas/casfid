<?php

namespace Tests\Unit\Services;

use App\Models\Pokemon;
use App\Services\PokemonService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * PokemonServiceTest
 *
 * Tests unitarios para PokemonService
 * Prueba métodos principales, caché y operaciones BD
 *
 * @package Tests\Unit\Services
 */
class PokemonServiceTest extends TestCase
{
    private PokemonService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PokemonService::class);
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }

    /**
     * Test: getPokemonList retorna estructura con data y paginación
     */
    public function test_get_pokemon_list_returns_paginated_data(): void
    {
        Http::fake([
            'pokeapi.co/api/v2/pokemon*' => Http::response([
                'count' => 1025,
                'results' => [
                    ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/'],
                    ['name' => 'ivysaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/2/'],
                ],
            ]),
        ]);

        $result = $this->service->getPokemonList(page: 1, perPage: 10);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertIsArray($result['data']);
        $this->assertIsArray($result['pagination']);
    }

    /**
     * Test: getPokemonDetail retorna datos correctos
     */
    public function test_get_pokemon_detail_returns_pokemon_data(): void
    {
        Http::fake([
            'pokeapi.co/api/v2/pokemon/1' => Http::response([
                'id' => 1,
                'name' => 'bulbasaur',
                'sprites' => [
                    'other' => [
                        'official-artwork' => [
                            'front_default' => 'https://example.com/bulbasaur.png',
                        ],
                    ],
                ],
                'types' => [
                    ['type' => ['name' => 'grass']],
                    ['type' => ['name' => 'poison']],
                ],
                'stats' => [
                    ['stat' => ['name' => 'hp'], 'base_stat' => 45],
                    ['stat' => ['name' => 'attack'], 'base_stat' => 49],
                    ['stat' => ['name' => 'defense'], 'base_stat' => 49],
                    ['stat' => ['name' => 'sp-attack'], 'base_stat' => 65],
                    ['stat' => ['name' => 'sp-defense'], 'base_stat' => 65],
                    ['stat' => ['name' => 'speed'], 'base_stat' => 45],
                ],
            ]),
            'pokeapi.co/api/v2/pokemon-species/1' => Http::response([
                'flavor_text_entries' => [
                    ['flavor_text' => 'A seed Pokémon.', 'language' => ['name' => 'en']],
                ],
            ]),
        ]);

        $pokemon = $this->service->getPokemonDetail(1);

        $this->assertIsArray($pokemon);
        $this->assertNotEmpty($pokemon);
        $this->assertEquals('Bulbasaur', $pokemon['name']);
    }

    /**
     * Test: getPokemonDetail lanza excepción con ID inválido
     */
    public function test_get_pokemon_detail_throws_on_invalid_id(): void
    {
        Http::fake([
            'pokeapi.co/api/v2/pokemon/9999' => Http::response([], 404),
        ]);

        $this->expectException(\Exception::class);
        $this->service->getPokemonDetail(9999);
    }

    /**
     * Test: getPokemonDetail usa caché (no hace llamadas duplicadas)
     */
    public function test_get_pokemon_detail_caches_result(): void
    {
        Http::fake([
            'pokeapi.co/api/v2/pokemon/25' => Http::response([
                'id' => 25,
                'name' => 'pikachu',
                'sprites' => [
                    'other' => ['official-artwork' => ['front_default' => 'https://example.com/pikachu.png']],
                ],
                'types' => [['type' => ['name' => 'electric']]],
                'stats' => array_fill(0, 6, ['stat' => ['name' => 'test'], 'base_stat' => 50]),
            ]),
            'pokeapi.co/api/v2/pokemon-species/25' => Http::response([
                'flavor_text_entries' => [
                    ['flavor_text' => 'Mouse Pokémon.', 'language' => ['name' => 'en']],
                ],
            ]),
        ]);

        // Primera llamada
        $pokemon1 = $this->service->getPokemonDetail(25);
        $requestCount1 = Http::recorded()->count();

        // Segunda llamada (desde caché)
        $pokemon2 = $this->service->getPokemonDetail(25);
        $requestCount2 = Http::recorded()->count();

        // No debe hacer más llamadas HTTP la segunda vez
        $this->assertEquals($requestCount1, $requestCount2);
    }

    /**
     * Test: Crear pokémon en BD con updateOrCreate
     */
    public function test_pokemon_created_in_database(): void
    {
        $pokemonData = [
            'pokedex_id' => 100,
            'name' => 'Voltorb',
            'types' => 'electric',
            'image_url' => 'https://example.com/100.png',
            'description' => 'Ball Pokémon',
            'hp' => 40,
            'attack' => 30,
            'defense' => 50,
            'sp_attack' => 55,
            'sp_defense' => 55,
            'speed' => 100,
        ];

        $pokemon = Pokemon::updateOrCreate(
            ['pokedex_id' => $pokemonData['pokedex_id']],
            $pokemonData
        );

        $this->assertNotNull($pokemon);
        $this->assertEquals(100, $pokemon->pokedex_id);
        $this->assertEquals('Voltorb', $pokemon->name);

        $this->assertDatabaseHas('pokemon', [
            'pokedex_id' => 100,
            'name' => 'Voltorb',
        ]);
    }

    /**
     * Test: Actualizar pokémon existente
     */
    public function test_pokemon_updated_in_database(): void
    {
        // Crear
        Pokemon::create([
            'pokedex_id' => 99,
            'name' => 'Kingler',
            'types' => 'water',
        ]);

        // Actualizar
        Pokemon::updateOrCreate(
            ['pokedex_id' => 99],
            ['name' => 'Kingler Updated', 'types' => 'water,bug']
        );

        $pokemon = Pokemon::where('pokedex_id', 99)->first();
        $this->assertEquals('Kingler Updated', $pokemon->name);
        $this->assertEquals('water,bug', $pokemon->types);
    }

    /**
     * Test: Buscar pokémon por pokedex_id
     */
    public function test_find_pokemon_by_pokedex_id(): void
    {
        Pokemon::create([
            'pokedex_id' => 50,
            'name' => 'Diglett',
            'types' => 'ground',
        ]);

        $pokemon = Pokemon::where('pokedex_id', 50)->first();

        $this->assertNotNull($pokemon);
        $this->assertEquals('Diglett', $pokemon->name);
        $this->assertEquals('ground', $pokemon->types);
    }

    /**
     * Test: Única instancia de pokémon por pokedex_id
     */
    public function test_pokemon_pokedex_id_unique(): void
    {
        Pokemon::create([
            'pokedex_id' => 75,
            'name' => 'Graveler',
            'types' => 'rock,ground',
        ]);

        $count = Pokemon::where('pokedex_id', 75)->count();
        $this->assertEquals(1, $count);
    }
}
