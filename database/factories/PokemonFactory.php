<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pokemon>
 */
class PokemonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['electric', 'fire', 'water', 'grass', 'poison', 'flying', 'bug', 'rock', 'ground', 'psychic', 'ice', 'dragon', 'dark', 'steel', 'fairy'];
        
        return [
            'pokedex_id' => $this->faker->unique()->numberBetween(1, 151),
            'name' => $this->faker->unique()->word(),
            'type' => $this->faker->randomElement($types),
            'hp' => $this->faker->numberBetween(20, 100),
            'attack' => $this->faker->numberBetween(20, 150),
            'defense' => $this->faker->numberBetween(20, 130),
            'sp_attack' => $this->faker->numberBetween(20, 154),
            'sp_defense' => $this->faker->numberBetween(20, 120),
            'speed' => $this->faker->numberBetween(5, 140),
            'image_url' => 'https://via.placeholder.com/300',
            'description' => $this->faker->sentence(),
        ];
    }
}
