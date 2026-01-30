<?php

namespace Tests\Unit\Services;

use App\Models\Favorite;
use App\Models\User;
use App\Services\FavoriteService;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\TestCase;
use Tests\TestCase as BaseTestCase;

/**
 * FavoriteServiceTest
 *
 * Tests unitarios para FavoriteService
 * Usa Mocks para simular llamadas HTTP a PokeAPI
 *
 * @package Tests\Unit\Services
 */
class FavoriteServiceTest extends BaseTestCase
{
    private FavoriteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(FavoriteService::class);
    }

    /**
     * Test: Agregar pokémon a favoritos exitosamente
     */
    public function test_add_to_favorites_success(): void
    {
        // Arrange
        $user = User::factory()->create();
        $pokemonId = 1;

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
        $favorite = $this->service->addToFavorites($user, $pokemonId);

        // Assert
        $this->assertNotNull($favorite);
        $this->assertEquals($user->id, $favorite->user_id);
        $this->assertEquals($pokemonId, $favorite->pokemon_id);
        $this->assertEquals('Bulbasaur', $favorite->pokemon_name);
        $this->assertStringContainsString('grass', $favorite->pokemon_type);
        $this->assertStringContainsString('poison', $favorite->pokemon_type);

        // Verificar que está en la base de datos
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'pokemon_id' => $pokemonId,
        ]);
    }

    /**
     * Test: Error cuando pokémon ya está en favoritos
     */
    public function test_add_to_favorites_conflict(): void
    {
        // Arrange
        $user = User::factory()->create();
        $pokemonId = 1;

        // Crear favorito existente
        Favorite::create([
            'user_id' => $user->id,
            'pokemon_id' => $pokemonId,
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

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->service->addToFavorites($user, $pokemonId);
    }

    /**
     * Test: Error cuando pokémon ID está fuera de rango
     */
    public function test_add_to_favorites_invalid_id(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->service->addToFavorites($user, 999);
    }

    /**
     * Test: Error cuando PokeAPI retorna 404
     */
    public function test_add_to_favorites_pokeapi_not_found(): void
    {
        // Arrange
        $user = User::factory()->create();
        $pokemonId = 1;

        Http::fake([
            'pokeapi.co/api/v2/pokemon/1' => Http::response([], 404),
        ]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->service->addToFavorites($user, $pokemonId);
    }

    /**
     * Test: Error cuando PokeAPI no responde (timeout)
     */
    public function test_add_to_favorites_pokeapi_timeout(): void
    {
        // Arrange
        $user = User::factory()->create();
        $pokemonId = 1;

        Http::fake([
            'pokeapi.co/api/v2/pokemon/1' => Http::response([], 500),
        ]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->service->addToFavorites($user, $pokemonId);
    }

    /**
     * Test: Eliminar pokémon de favoritos exitosamente
     */
    public function test_remove_from_favorites_success(): void
    {
        // Arrange
        $user = User::factory()->create();
        $favorite = Favorite::factory()->create(['user_id' => $user->id]);

        // Act
        $result = $this->service->removeFromFavorites($user, $favorite->pokemon_id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('favorites', [
            'id' => $favorite->id,
        ]);
    }

    /**
     * Test: Error cuando favorito no existe
     */
    public function test_remove_from_favorites_not_found(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->service->removeFromFavorites($user, 999);
    }

    /**
     * Test: Obtener favoritos del usuario
     */
    public function test_get_favorites(): void
    {
        // Arrange
        $user = User::factory()->create();
        $favorites = Favorite::factory(3)->create(['user_id' => $user->id]);

        // Act
        $result = $this->service->getFavorites($user);

        // Assert
        $this->assertCount(3, $result);
        $this->assertEquals($favorites[0]->pokemon_id, $result[0]->pokemon_id);
    }

    /**
     * Test: Favoritos vacío si usuario no tiene
     */
    public function test_get_favorites_empty(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $result = $this->service->getFavorites($user);

        // Assert
        $this->assertCount(0, $result);
    }

    /**
     * Test: Verificar si pokémon está en favoritos
     */
    public function test_is_favorite_true(): void
    {
        // Arrange
        $user = User::factory()->create();
        $favorite = Favorite::factory()->create(['user_id' => $user->id]);

        // Act
        $result = $this->service->isFavorite($user, $favorite->pokemon_id);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test: Verificar si pokémon NO está en favoritos
     */
    public function test_is_favorite_false(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $result = $this->service->isFavorite($user, 999);

        // Assert
        $this->assertFalse($result);
    }

    /**
     * Test: Mock de PokeAPI con múltiples tipos
     */
    public function test_add_to_favorites_multiple_types(): void
    {
        // Arrange
        $user = User::factory()->create();
        $pokemonId = 25; // Pikachu

        Http::fake([
            'pokeapi.co/api/v2/pokemon/25' => Http::response([
                'id' => 25,
                'name' => 'pikachu',
                'types' => [
                    ['type' => ['name' => 'electric']],
                ],
            ]),
        ]);

        // Act
        $favorite = $this->service->addToFavorites($user, $pokemonId);

        // Assert
        $this->assertEquals('Pikachu', $favorite->pokemon_name);
        $this->assertEquals('electric', $favorite->pokemon_type);
    }

    /**
     * Test: Validar que PokeAPI es llamado correctamente
     */
    public function test_pokeapi_called_correctly(): void
    {
        // Arrange
        $user = User::factory()->create();
        $pokemonId = 6; // Charizard

        Http::fake([
            'https://pokeapi.co/api/v2/pokemon/6' => Http::response([
                'id' => 6,
                'name' => 'charizard',
                'types' => [
                    ['type' => ['name' => 'fire']],
                    ['type' => ['name' => 'flying']],
                ],
            ]),
        ]);

        // Act
        $this->service->addToFavorites($user, $pokemonId);

        // Assert
        Http::assertSent(function ($request) {
            return $request->url() === 'https://pokeapi.co/api/v2/pokemon/6';
        });
    }
}
