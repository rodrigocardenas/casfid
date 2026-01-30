<?php

namespace Tests\Feature\Controllers;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * FavoriteControllerTest
 *
 * Tests de integración para FavoriteController
 * Prueba endpoints HTTP completos
 *
 * @package Tests\Feature\Controllers
 */
class FavoriteControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Limpiar el caché antes de cada test
        \Illuminate\Support\Facades\Cache::flush();

        // Crear usuario y token
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        // Generar token en formato userid.random.timestamp
        // basado en el método generateToken del middleware AuthToken
        $this->token = $this->user->id . '.' . \Illuminate\Support\Str::random(40) . '.' . now()->timestamp;
    }

    protected function tearDown(): void
    {
        // Limpiar el caché después de cada test
        \Illuminate\Support\Facades\Cache::flush();
        parent::tearDown();
    }

    /**
     * Helper para generar mock de PokeAPI completo
     */
    private function mockPokemonFromPokeAPI(int $id, string $name = null, array $types = null): array
    {
        $name = $name ?? ['bulbasaur', 'charmander', 'squirtle', 'pikachu'][$id - 1] ?? 'pokemon' . $id;
        $types = $types ?? ($id === 1 ? [['type' => ['name' => 'grass']], ['type' => ['name' => 'poison']]] : [['type' => ['name' => 'normal']]]);

        return [
            'id' => $id,
            'name' => $name,
            'types' => $types,
            'sprites' => [
                'front_default' => "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/{$id}.png",
                'other' => [
                    'official-artwork' => [
                        'front_default' => "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/{$id}.png"
                    ]
                ]
            ],
            'stats' => [
                ['stat' => ['name' => 'hp'], 'base_stat' => 45],
                ['stat' => ['name' => 'attack'], 'base_stat' => 49],
                ['stat' => ['name' => 'defense'], 'base_stat' => 49],
                ['stat' => ['name' => 'sp-atk'], 'base_stat' => 65],
                ['stat' => ['name' => 'sp-def'], 'base_stat' => 65],
                ['stat' => ['name' => 'speed'], 'base_stat' => 45],
            ],
        ];
    }

    /**
     * Test: POST /favorites exitoso
     */
    public function test_post_favorites_success(): void
    {
        // Arrange - Pre-crear Pokémon para evitar PokeAPI
        \App\Models\Pokemon::updateOrCreate(
            ['pokedex_id' => 1],
            ['name' => 'Bulbasaur', 'type' => 'grass,poison']
        );

        // Act
        $response = $this->postJson('/api/v1/favorites', [
            'pokemon_id' => 1,
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        // Assert
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'user_id',
                'pokemon_id',
                'pokemon_name',
                'pokemon_type',
            ],
            'message',
            'timestamp',
        ]);
        $response->assertJson([
            'success' => true,
            'message' => 'Pokemon added to favorites',
        ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'pokemon_id' => 1,
        ]);
    }

    /**
     * Test: POST /favorites sin autenticación
     */
    public function test_post_favorites_unauthorized(): void
    {
        // Act
        $response = $this->postJson('/api/v1/favorites', [
            'pokemon_id' => 1,
        ]);

        // Assert
        $response->assertStatus(401);
    }

    /**
     * Test: POST /favorites con ID duplicado
     */
    public function test_post_favorites_conflict(): void
    {
        // Arrange - Pre-crear Pokémon
        $pokemon = \App\Models\Pokemon::updateOrCreate(
            ['pokedex_id' => 2],
            ['name' => 'Ivysaur', 'type' => 'grass,poison']
        );

        Favorite::create([
            'user_id' => $this->user->id,
            'pokemon_id' => $pokemon->id,
            'pokemon_name' => $pokemon->name,
            'pokemon_type' => $pokemon->type,
        ]);

        // Act - No necesita mock HTTP porque validatePokemonExists busca en BD primero
        $response = $this->postJson('/api/v1/favorites', [
            'pokemon_id' => 2,
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        // Assert
        $response->assertStatus(409);
        $response->assertJson([
            'success' => false,
            'error' => 'Pokemon already in favorites',
        ]);
    }

    /**
     * Test: POST /favorites con ID inválido
     */
    public function test_post_favorites_invalid_id(): void
    {
        // Arrange - Mock PokeAPI para devolver error en ID inválido
        // Primero validamos que el servicio rechace el ID directamente (< 1 o > 150)
        // Como 999 es > 150, debe rechazarse sin llamar a PokeAPI

        // Act - Laravel valida max:150 y devuelve 422
        $response = $this->postJson('/api/v1/favorites', [
            'pokemon_id' => 999,
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        // Assert - Esperamos 422 de validación de Laravel
        $response->assertStatus(422);
    }

    /**
     * Test: POST /favorites sin pokemon_id
     */
    public function test_post_favorites_missing_pokemon_id(): void
    {
        // Act
        $response = $this->postJson('/api/v1/favorites', [], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        // Assert
        $response->assertStatus(422);
    }

    /**
     * Test: DELETE /favorites/{pokemon_id} exitoso
     */
    public function test_delete_favorite_success(): void
    {
        // Arrange
        $pokemon = \App\Models\Pokemon::updateOrCreate(
            ['pokedex_id' => 100],
            ['name' => 'Voltorb', 'type' => 'electric']
        );
        $favorite = Favorite::create([
            'user_id' => $this->user->id,
            'pokemon_id' => $pokemon->id,
            'pokemon_name' => $pokemon->name,
            'pokemon_type' => $pokemon->type,
        ]);

        // Act
        $response = $this->deleteJson("/api/v1/favorites/{$pokemon->pokedex_id}", [], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Pokemon removed from favorites',
        ]);

        $this->assertDatabaseMissing('favorites', [
            'id' => $favorite->id,
        ]);
    }

    /**
     * Test: DELETE /favorites/{pokemon_id} no encontrado
     */
    public function test_delete_favorite_not_found(): void
    {
        // Arrange - Pre-crear el Pokemon pero sin favorito
        \App\Models\Pokemon::updateOrCreate(
            ['pokedex_id' => 50],
            ['name' => 'Diglett', 'type' => 'ground']
        );

        // Act - Intentar eliminar un favorito que no existe (pokémon 50 existe pero sin favorito)
        $response = $this->deleteJson("/api/v1/favorites/50", [], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        // Assert
        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'error' => 'Favorite not found',
        ]);
    }

    /**
     * Test: DELETE /favorites/{pokemon_id} sin autenticación
     */
    public function test_delete_favorite_unauthorized(): void
    {
        // Act
        $response = $this->deleteJson("/api/v1/favorites/1");

        // Assert
        $response->assertStatus(401);
    }

    /**
     * Test: GET /favorites listado exitoso
     */
    public function test_get_favorites_success(): void
    {
        // Arrange
        for ($i = 101; $i <= 103; $i++) {
            $pokemon = \App\Models\Pokemon::updateOrCreate(
                ['pokedex_id' => $i],
                ['name' => 'Pokemon' . $i, 'type' => 'normal']
            );
            Favorite::create([
                'user_id' => $this->user->id,
                'pokemon_id' => $pokemon->id,
                'pokemon_name' => $pokemon->name,
                'pokemon_type' => $pokemon->type,
            ]);
        }
        $favorites = Favorite::where('user_id', $this->user->id)->get();

        // Act
        $response = $this->getJson('/api/v1/favorites', [
            'Authorization' => "Bearer {$this->token}",
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'pokemon_id',
                    'pokemon_name',
                    'pokemon_type',
                ],
            ],
            'pagination',
            'timestamp',
        ]);
        $response->assertJson([
            'success' => true,
            'pagination' => [
                'total' => 3,
            ],
        ]);
    }

    /**
     * Test: GET /favorites vacío
     */
    public function test_get_favorites_empty(): void
    {
        // Act
        $response = $this->getJson('/api/v1/favorites', [
            'Authorization' => "Bearer {$this->token}",
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [],
            'pagination' => [
                'total' => 0,
            ],
        ]);
    }

    /**
     * Test: GET /favorites sin autenticación
     */
    public function test_get_favorites_unauthorized(): void
    {
        // Act
        $response = $this->getJson('/api/v1/favorites');

        // Assert
        $response->assertStatus(401);
    }

    /**
     * Test: GET /favorites con paginación
     */
    public function test_get_favorites_pagination(): void
    {
        // Arrange
        for ($i = 1; $i <= 25; $i++) {
            $pokemon = \App\Models\Pokemon::updateOrCreate(
                ['pokedex_id' => $i],
                ['name' => 'Pokemon' . $i, 'type' => 'normal']
            );
            Favorite::create([
                'user_id' => $this->user->id,
                'pokemon_id' => $pokemon->id,
                'pokemon_name' => $pokemon->name,
                'pokemon_type' => $pokemon->type,
            ]);
        }

        // Act - Primera página
        $response1 = $this->getJson('/api/v1/favorites?page=1&per_page=10', [
            'Authorization' => "Bearer {$this->token}",
        ]);

        // Assert
        $response1->assertStatus(200);
        $response1->assertJson([
            'pagination' => [
                'current_page' => 1,
                'per_page' => 10,
                'total' => 25,
                'total_pages' => 3,
            ],
        ]);

        // Act - Segunda página
        $response2 = $this->getJson('/api/v1/favorites?page=2&per_page=10', [
            'Authorization' => "Bearer {$this->token}",
        ]);

        // Assert
        $response2->assertStatus(200);
        $response2->assertJson([
            'pagination' => [
                'current_page' => 2,
            ],
        ]);
    }

    /**
     * Test: GET /favorites página inválida
     */
    public function test_get_favorites_invalid_page(): void
    {
        // Arrange
        for ($i = 26; $i <= 30; $i++) {
            $pokemon = \App\Models\Pokemon::updateOrCreate(
                ['pokedex_id' => $i],
                ['name' => 'Pokemon' . $i, 'type' => 'normal']
            );
            Favorite::create([
                'user_id' => $this->user->id,
                'pokemon_id' => $pokemon->id,
                'pokemon_name' => $pokemon->name,
                'pokemon_type' => $pokemon->type,
            ]);
        }

        // Act
        $response = $this->getJson('/api/v1/favorites?page=999', [
            'Authorization' => "Bearer {$this->token}",
        ]);

        // Assert
        $response->assertStatus(404);
    }

    /**
     * Test: Favoritos no se comparten entre usuarios
     */
    public function test_favorites_isolated_by_user(): void
    {
        // Arrange
        $user2 = User::factory()->create();
        
        // Crear favorito para user1
        $pokemon1 = \App\Models\Pokemon::updateOrCreate(
            ['pokedex_id' => 31],
            ['name' => 'Pokemon31', 'type' => 'normal']
        );
        $favorite1 = Favorite::create([
            'user_id' => $this->user->id,
            'pokemon_id' => $pokemon1->id,
            'pokemon_name' => $pokemon1->name,
            'pokemon_type' => $pokemon1->type,
        ]);
        
        // Crear favorito para user2
        $pokemon2 = \App\Models\Pokemon::updateOrCreate(
            ['pokedex_id' => 32],
            ['name' => 'Pokemon32', 'type' => 'normal']
        );
        Favorite::create([
            'user_id' => $user2->id,
            'pokemon_id' => $pokemon2->id,
            'pokemon_name' => $pokemon2->name,
            'pokemon_type' => $pokemon2->type,
        ]);

        // Generar token para user2 en mismo formato que setUp()
        $token2 = $user2->id . '.' . \Illuminate\Support\Str::random(40) . '.' . now()->timestamp;

        // Act
        $response = $this->getJson('/api/v1/favorites', [
            'Authorization' => "Bearer {$this->token}",
        ]);

        // Assert
        $response->assertJson([
            'pagination' => [
                'total' => 1,
            ],
        ]);

        // Verificar que el otro usuario no ve los favoritos del primero
        $response2 = $this->getJson('/api/v1/favorites', [
            'Authorization' => "Bearer {$token2}",
        ]);

        $response2->assertJson([
            'pagination' => [
                'total' => 1,
            ],
        ]);
    }

    /**
     * Test: Integración completa (add → list → delete)
     */
    public function test_favorites_complete_flow(): void
    {
        // Pre-crear un Pokémon en BD (para evitar PokeAPI call)
        $pokemon = \App\Models\Pokemon::updateOrCreate(
            ['pokedex_id' => 150],
            ['name' => 'Mewtwo', 'type' => 'psychic']
        );
        
        // Verificar que el pokémon se creó correctamente
        $this->assertNotNull($pokemon);
        $this->assertEquals(150, $pokemon->pokedex_id);
        
        // Verificar que se puede encontrar en BD
        $foundPokemon = \App\Models\Pokemon::where('pokedex_id', 150)->first();
        $this->assertNotNull($foundPokemon);

        // 1. Agregar favorito
        $addResponse = $this->postJson('/api/v1/favorites', [
            'pokemon_id' => 150,
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);
        
        if ($addResponse->status() !== 201) {
            dd([
                'status' => $addResponse->status(),
                'response' => $addResponse->json(),
            ]);
        }
        
        $this->assertEquals(201, $addResponse->status());

        // 2. Verificar que está en la lista
        $listResponse = $this->getJson('/api/v1/favorites', [
            'Authorization' => "Bearer {$this->token}",
        ]);
        $this->assertEquals(200, $listResponse->status());
        $this->assertEquals(1, $listResponse->json('pagination.total'));

        // 3. Eliminar favorito
        $deleteResponse = $this->deleteJson('/api/v1/favorites/150', [], [
            'Authorization' => "Bearer {$this->token}",
        ]);
        $this->assertEquals(200, $deleteResponse->status());

        // 4. Verificar que fue eliminado
        $finalListResponse = $this->getJson('/api/v1/favorites', [
            'Authorization' => "Bearer {$this->token}",
        ]);
        $this->assertEquals(0, $finalListResponse->json('pagination.total'));
    }
}
