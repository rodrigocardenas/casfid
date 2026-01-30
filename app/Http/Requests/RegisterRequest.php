<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-záéíóúñA-ZÁÉÍÓÚÑ\s\-\.\']+$/',
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'unique:users,email',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&._-]+$/',
                'confirmed',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido.',
            'name.string' => 'El nombre debe ser texto.',
            'name.min' => 'El nombre debe tener mínimo 2 caracteres.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'name.regex' => 'El nombre contiene caracteres inválidos.',
            
            'email.required' => 'El email es requerido.',
            'email.email' => 'El email no es válido.',
            'email.unique' => 'Este email ya está registrado.',
            'email.max' => 'El email no puede exceder 255 caracteres.',
            
            'password.required' => 'La contraseña es requerida.',
            'password.string' => 'La contraseña debe ser texto.',
            'password.min' => 'La contraseña debe tener mínimo 8 caracteres.',
            'password.max' => 'La contraseña no puede exceder 255 caracteres.',
            'password.regex' => 'La contraseña debe contener mayúscula, minúscula y número.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'email',
            'password' => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
        ];
    }
}
