<?php

namespace Tests\Feature\Favorites;

use App\Models\User;
use App\Models\Pokemon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddFavoriteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Pokemon $pokemon;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->pokemon = Pokemon::factory()->create();
    }

    /**
     * Test adding pokemon to favorites
     */
    public function test_can_add_pokemon_to_favorites(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/favorites', [
                'pokemon_id' => $this->pokemon->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['is_favorite' => true]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'pokemon_id' => $this->pokemon->id,
        ]);
    }

    /**
     * Test cannot add same pokemon twice (duplicate prevention)
     */
    public function test_cannot_add_same_pokemon_twice(): void
    {
        // Add first time
        $this->actingAs($this->user)
            ->postJson('/api/favorites', [
                'pokemon_id' => $this->pokemon->id,
            ])->assertStatus(201);

        // Try add second time
        $response = $this->actingAs($this->user)
            ->postJson('/api/favorites', [
                'pokemon_id' => $this->pokemon->id,
            ]);

        $response->assertStatus(409)
            ->assertJsonFragment(['message' => 'Pokemon already in favorites']);
    }

    /**
     * Test requires authentication
     */
    public function test_requires_authentication(): void
    {
        $response = $this->postJson('/api/favorites', [
            'pokemon_id' => $this->pokemon->id,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test validation errors
     */
    public function test_validates_pokemon_id(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/favorites', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pokemon_id']);
    }

    /**
     * Test adding non-existent pokemon
     */
    public function test_cannot_add_non_existent_pokemon(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/favorites', [
                'pokemon_id' => 99999,
            ]);

        $response->assertStatus(404);
    }

    /**
     * Test user can favorite multiple pokemon
     */
    public function test_can_favorite_multiple_pokemon(): void
    {
        $pokemon1 = Pokemon::factory()->create();
        $pokemon2 = Pokemon::factory()->create();

        $this->actingAs($this->user)
            ->postJson('/api/favorites', ['pokemon_id' => $pokemon1->id])
            ->assertStatus(201);

        $this->actingAs($this->user)
            ->postJson('/api/favorites', ['pokemon_id' => $pokemon2->id])
            ->assertStatus(201);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'pokemon_id' => $pokemon1->id,
        ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'pokemon_id' => $pokemon2->id,
        ]);
    }
}
