<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JwtAuth\Facades\JwtAuth;
use Tymon\JwtAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $token = JwtAuth::parseToken();
            $token->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JwtAuth\Exceptions\TokenNotProvidedException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token no proporcionado',
                ], 401);
            } elseif ($e instanceof \Tymon\JwtAuth\Exceptions\TokenExpiredException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token expirado',
                ], 401);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido',
                ], 401);
            }
        }

        return $next($request);
    }
}
