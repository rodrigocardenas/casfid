<?php

namespace Tests\Feature\Favorites;

use App\Models\User;
use App\Models\Pokemon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveFavoriteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Pokemon $pokemon;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->pokemon = Pokemon::factory()->create();
        
        // Add pokemon to favorites initially
        $this->user->favorites()->attach($this->pokemon->id);
    }

    /**
     * Test removing pokemon from favorites
     */
    public function test_can_remove_pokemon_from_favorites(): void
    {
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/favorites/{$this->pokemon->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['is_favorite' => false]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'pokemon_id' => $this->pokemon->id,
        ]);
    }

    /**
     * Test removing non-favorited pokemon returns 404
     */
    public function test_removing_non_favorited_pokemon_returns_404(): void
    {
        $otherPokemon = Pokemon::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/favorites/{$otherPokemon->id}");

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'Not found in favorites']);
    }

    /**
     * Test requires authentication
     */
    public function test_requires_authentication(): void
    {
        $response = $this->deleteJson("/api/favorites/{$this->pokemon->id}");

        $response->assertStatus(401);
    }

    /**
     * Test can remove multiple favorites
     */
    public function test_can_remove_multiple_favorites(): void
    {
        $pokemon2 = Pokemon::factory()->create();
        $this->user->favorites()->attach($pokemon2->id);

        $this->actingAs($this->user)
            ->deleteJson("/api/favorites/{$this->pokemon->id}")
            ->assertStatus(200);

        $this->actingAs($this->user)
            ->deleteJson("/api/favorites/{$pokemon2->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'pokemon_id' => $this->pokemon->id,
        ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'pokemon_id' => $pokemon2->id,
        ]);
    }

    /**
     * Test cannot remove favorite twice
     */
    public function test_cannot_remove_favorite_twice(): void
    {
        $this->actingAs($this->user)
            ->deleteJson("/api/favorites/{$this->pokemon->id}")
            ->assertStatus(200);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/favorites/{$this->pokemon->id}");

        $response->assertStatus(404);
    }
}
