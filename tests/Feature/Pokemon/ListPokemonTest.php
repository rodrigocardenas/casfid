<?php

namespace Tests\Feature\Pokemon;

use App\Models\User;
use App\Models\Pokemon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListPokemonTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test listing all pokemon with pagination
     */
    public function test_can_list_pokemon_with_pagination(): void
    {
        Pokemon::factory(25)->create();

        $response = $this->getJson('/api/pokemon?page=1&per_page=12');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'types', 'image', 'is_favorite']
                ],
                'pagination' => ['current_page', 'per_page', 'total']
            ])
            ->assertJsonCount(12, 'data');
    }

    /**
     * Test searching pokemon by name
     */
    public function test_can_search_pokemon_by_name(): void
    {
        Pokemon::factory()->create(['name' => 'pikachu']);
        Pokemon::factory()->create(['name' => 'pichu']);
        Pokemon::factory(5)->create(['name' => 'charmander']);

        $response = $this->getJson('/api/pokemon?search=pika');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Test filtering pokemon by type
     */
    public function test_can_filter_pokemon_by_type(): void
    {
        Pokemon::factory(3)->create();
        // Note: Adjust based on your actual type structure

        $response = $this->getJson('/api/pokemon?type=electric');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'pagination']);
    }

    /**
     * Test getting single pokemon
     */
    public function test_can_get_single_pokemon(): void
    {
        $pokemon = Pokemon::factory()->create([
            'name' => 'pikachu',
        ]);

        $response = $this->getJson("/api/pokemon/{$pokemon->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'pikachu'])
            ->assertJsonStructure(['id', 'name', 'types', 'image', 'stats']);
    }

    /**
     * Test 404 for non-existent pokemon
     */
    public function test_returns_404_for_non_existent_pokemon(): void
    {
        $response = $this->getJson('/api/pokemon/99999');

        $response->assertStatus(404);
    }

    /**
     * Test pagination with custom page
     */
    public function test_can_paginate_with_custom_page(): void
    {
        Pokemon::factory(30)->create();

        $response = $this->getJson('/api/pokemon?page=2&per_page=12');

        $response->assertStatus(200)
            ->assertJsonCount(12, 'data')
            ->assertJsonPath('pagination.current_page', 2);
    }

    /**
     * Test is_favorite field for authenticated user
     */
    public function test_is_favorite_field_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $pokemon = Pokemon::factory()->create();

        // Add to favorites
        $user->favorites()->attach($pokemon->id);

        $response = $this->actingAs($user)
            ->getJson("/api/pokemon/{$pokemon->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['is_favorite' => true]);
    }

    /**
     * Test is_favorite is false for non-favorite pokemon
     */
    public function test_is_favorite_false_for_non_favorite_pokemon(): void
    {
        $user = User::factory()->create();
        $pokemon = Pokemon::factory()->create();

        $response = $this->actingAs($user)
            ->getJson("/api/pokemon/{$pokemon->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['is_favorite' => false]);
    }
}
