<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pokemon extends Model
{
    /** @use HasFactory<\Database\Factories\PokemonFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'pokedex_id',
        'type',
        'hp',
        'attack',
        'defense',
        'sp_attack',
        'sp_defense',
        'speed',
        'image_url',
        'description',
    ];

    protected $casts = [
        'hp' => 'integer',
        'attack' => 'integer',
        'defense' => 'integer',
        'sp_attack' => 'integer',
        'sp_defense' => 'integer',
        'speed' => 'integer',
    ];

    /**
     * Get the users who favorited this pokemon
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites', 'pokemon_id', 'user_id');
    }
}
