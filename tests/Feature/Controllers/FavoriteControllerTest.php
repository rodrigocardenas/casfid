<?php

namespace Tests\Feature\Controllers;

use App\Models\Favorite;
use App\Models\User;
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
    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear usuario y token
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        // Generar token JWT
        $this->token = auth('api')->fromUser($this->user);
    }

    /**
     * Test: POST /favorites exitoso
     */
    public function test_post_favorites_success(): void
    {
        // Arrange
        Http::fake([
            'pokeapi.co/api/v2/pokemon/1' => Http::response([
                'id' => 1,
                'name' => 'bulbasaur',
                'types' => [
                    ['type' => ['name' => 'grass']],
                    ['type' => ['name' => 'poison']],
                ],
            ]),
        ]);

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
        // Arrange
        Favorite::create([
            'user_id' => $this->user->id,
            'pokemon_id' => 1,
            'pokemon_name' => 'Bulbasaur',
            'pokemon_type' => 'grass,poison',
        ]);

        Http::fake([
            'pokeapi.co/api/v2/pokemon/1' => Http::response([
                'id' => 1,
                'name' => 'bulbasaur',
                'types' => [
                    ['type' => ['name' => 'grass']],
                    ['type' => ['name' => 'poison']],
                ],
            ]),
        ]);

        // Act
        $response = $this->postJson('/api/v1/favorites', [
            'pokemon_id' => 1,
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
        // Act
        $response = $this->postJson('/api/v1/favorites', [
            'pokemon_id' => 999,
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);

        // Assert
        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
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
        $favorite = Favorite::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Act
        $response = $this->deleteJson("/api/v1/favorites/{$favorite->pokemon_id}", [], [
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
        // Act
        $response = $this->deleteJson("/api/v1/favorites/999", [], [
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
        $favorites = Favorite::factory(3)->create([
            'user_id' => $this->user->id,
        ]);

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
        Favorite::factory(25)->create(['user_id' => $this->user->id]);

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
        Favorite::factory(5)->create(['user_id' => $this->user->id]);

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
        $favorite1 = Favorite::factory()->create(['user_id' => $this->user->id]);
        Favorite::factory()->create(['user_id' => $user2->id]);

        $token2 = auth('api')->fromUser($user2);

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
        // Mock PokeAPI
        Http::fake([
            'pokeapi.co/api/v2/pokemon/*' => Http::response([
                'id' => 1,
                'name' => 'bulbasaur',
                'types' => [
                    ['type' => ['name' => 'grass']],
                    ['type' => ['name' => 'poison']],
                ],
            ]),
        ]);

        // 1. Agregar favorito
        $addResponse = $this->postJson('/api/v1/favorites', [
            'pokemon_id' => 1,
        ], [
            'Authorization' => "Bearer {$this->token}",
        ]);
        $this->assertEquals(201, $addResponse->status());

        // 2. Verificar que está en la lista
        $listResponse = $this->getJson('/api/v1/favorites', [
            'Authorization' => "Bearer {$this->token}",
        ]);
        $this->assertEquals(200, $listResponse->status());
        $this->assertEquals(1, $listResponse->json('pagination.total'));

        // 3. Eliminar favorito
        $deleteResponse = $this->deleteJson('/api/v1/favorites/1', [], [
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
