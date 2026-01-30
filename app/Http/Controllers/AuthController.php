<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Generate a simple token for testing (temporary - replace with JWT later)
     */
    private function generateToken(User $user): string
    {
        return hash('sha256', $user->id . '.' . Str::random(40) . '.' . now()->timestamp);
    }

    /**
     * Registrar nuevo usuario
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Crear usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Generar token
            $token = $this->generateToken($user);

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->toIso8601String(),
                ],
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Iniciar sesión de usuario
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Buscar usuario por email
            $user = User::byEmail($request->email)->first();

            // Validar que el usuario existe y la contraseña es correcta
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales inválidas',
                ], 401);
            }

            // Generar token
            $token = $this->generateToken($user);

            return response()->json([
                'success' => true,
                'message' => 'Autenticación exitosa',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
                'expires_in' => 3600, // 1 hora en segundos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al autenticar usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cerrar sesión de usuario
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Renovar token
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        try {
            // For testing purposes, just return a new token
            $user = auth('api')->user() ?? auth('web')->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado',
                ], 401);
            }

            $newToken = $this->generateToken($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $newToken,
                    'expires_in' => 3600,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al renovar token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener usuario autenticado
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        try {
            $user = auth('api')->user() ?? auth('web')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->toIso8601String(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
