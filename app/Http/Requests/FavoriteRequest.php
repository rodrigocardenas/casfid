<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FavoriteRequest
 * 
 * Validación para POST /api/v1/favorites
 * 
 * @package App\Http\Requests
 */
class FavoriteRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado a hacer esta petición
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Validación por JWT en middleware
    }

    /**
     * Obtener las reglas de validación
     * 
     * @return array
     */
    public function rules(): array
    {
        return [
            'pokemon_id' => 'required|integer|min:1|max:150',
        ];
    }

    /**
     * Mensajes de validación personalizados
     * 
     * @return array
     */
    public function messages(): array
    {
        return [
            'pokemon_id.required' => 'El ID del pokémon es requerido',
            'pokemon_id.integer' => 'El ID del pokémon debe ser un número entero',
            'pokemon_id.min' => 'El ID del pokémon debe ser mayor a 0',
            'pokemon_id.max' => 'El ID del pokémon debe ser menor o igual a 150',
        ];
    }

    /**
     * Nombres de atributos personalizados
     * 
     * @return array
     */
    public function attributes(): array
    {
        return [
            'pokemon_id' => 'ID del pokémon',
        ];
    }
}
