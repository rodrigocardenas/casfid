<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AuthToken
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener el token del header Authorization
        $token = $request->bearerToken();

        Log::debug('AuthToken middleware - Token:', ['token' => substr($token ?? '', 0, 20)]);

        if (!$token) {
            Log::warning('AuthToken - No token provided');
            return response()->json([
                'success' => false,
                'message' => 'Token de autorización no proporcionado',
            ], 401);
        }

        // El token tiene formato: userid.random.timestamp (basado en generateToken)
        // Extraer el user_id del token
        $parts = explode('.', $token);
        Log::debug('AuthToken - Token parts:', ['count' => count($parts), 'first' => $parts[0] ?? null]);

        if (count($parts) >= 1 && is_numeric($parts[0])) {
            $userId = (int)$parts[0];
            Log::debug('AuthToken - Extracted userId:', ['userId' => $userId]);
            
            $user = User::find($userId);

            if ($user) {
                Log::debug('AuthToken - User found:', ['userId' => $user->id, 'email' => $user->email]);
                
                // Establecer el usuario en el request
                $request->setUserResolver(function () use ($user) {
                    return $user;
                });
                
                // Establecer el usuario en el auth guard web
                Auth::guard('web')->setUser($user);
                
                Log::debug('AuthToken - Auth::user() after setUser:', ['user' => Auth::user() ? Auth::user()->id : 'null']);
                
                return $next($request);
            } else {
                Log::warning('AuthToken - User not found:', ['userId' => $userId]);
            }
        } else {
            Log::warning('AuthToken - Invalid token format');
        }

        return response()->json([
            'success' => false,
            'message' => 'Usuario no encontrado o token inválido',
        ], 401);
    }
}
