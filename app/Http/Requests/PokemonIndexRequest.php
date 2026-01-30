<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PokemonIndexRequest
 * 
 * Validación de parámetros para GET /api/v1/pokemon
 * 
 * @package App\Http\Requests
 */
class PokemonIndexRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado a hacer esta petición
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Endpoint público
    }

    /**
     * Obtener las reglas de validación que se aplican a la solicitud
     * 
     * @return array
     */
    public function rules(): array
    {
        return [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:50',
            'type' => 'string|max:20|nullable',
            'search' => 'string|max:100|nullable',
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
            'page.integer' => 'La página debe ser un número entero',
            'page.min' => 'La página debe ser mayor a 0',
            'per_page.integer' => 'Items por página debe ser un número entero',
            'per_page.min' => 'Items por página debe ser mayor a 0',
            'per_page.max' => 'Items por página no puede ser mayor a 50',
            'type.string' => 'El tipo debe ser una cadena de texto',
            'type.max' => 'El tipo no puede exceder 20 caracteres',
            'search.string' => 'La búsqueda debe ser una cadena de texto',
            'search.max' => 'La búsqueda no puede exceder 100 caracteres',
        ];
    }

    /**
     * Nombres de atributos personalizados para mensajes
     * 
     * @return array
     */
    public function attributes(): array
    {
        return [
            'page' => 'página',
            'per_page' => 'items por página',
            'type' => 'tipo de Pokémon',
            'search' => 'búsqueda',
        ];
    }
}
