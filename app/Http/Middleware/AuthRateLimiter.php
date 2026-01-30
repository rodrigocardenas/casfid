<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthRateLimiter
{
    /**
     * The rate limiter instance.
     *
     * @var RateLimiter
     */
    protected RateLimiter $limiter;

    /**
     * Create a new middleware instance.
     *
     * @param RateLimiter $limiter
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Rate limiting para login: 5 intentos cada 15 minutos por IP
        if ($request->is('*/auth/login')) {
            $key = 'login_' . $request->ip();
            $maxAttempts = 5;
            $decayMinutes = 15;

            if ($this->limiter->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Demasiados intentos de inicio de sesión. Intenta más tarde.',
                    'retry_after' => $this->limiter->availableIn($key),
                ], 429);
            }

            $this->limiter->hit($key, $decayMinutes * 60);
        }

        // Rate limiting para register: 3 intentos cada 60 minutos por IP
        if ($request->is('*/auth/register')) {
            $key = 'register_' . $request->ip();
            $maxAttempts = 3;
            $decayMinutes = 60;

            if ($this->limiter->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Demasiados intentos de registro. Intenta más tarde.',
                    'retry_after' => $this->limiter->availableIn($key),
                ], 429);
            }

            $this->limiter->hit($key, $decayMinutes * 60);
        }

        return $next($request);
    }
}
