<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Favorite>
 */
class FavoriteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generar un pokémon_id basado en el timestamp para mayor "unicidad"
        // pero manteniendo randomness
        $pokemonId = ((int)(microtime(true) * 10000)) % 150 + 1;
        
        // Crear o actualizar el Pokémon
        $pokemon = \App\Models\Pokemon::updateOrCreate(
            ['pokedex_id' => $pokemonId],
            [
                'name' => 'Pokemon' . $pokemonId,
                'type' => 'normal',
            ]
        );

        return [
            'user_id' => \App\Models\User::factory(),
            'pokemon_id' => $pokemon->id,
            'pokemon_name' => $pokemon->name,
            'pokemon_type' => $pokemon->type,
        ];
    }
}
