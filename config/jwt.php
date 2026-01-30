<?php

/*
|--------------------------------------------------------------------------
| JWT Configuration
|--------------------------------------------------------------------------
|
| Configuración para autenticación con JWT usando tymon/jwt-auth
|
*/

return [
    /*
    |--------------------------------------------------------------------------
    | Algorithm
    |--------------------------------------------------------------------------
    |
    | El algoritmo a usar para firmar los tokens.
    | Disponibles: 'HS256', 'HS512', 'RS256'
    |
    */
    'algorithm' => env('JWT_ALGORITHM', 'HS256'),

    /*
    |--------------------------------------------------------------------------
    | Secret
    |--------------------------------------------------------------------------
    |
    | La clave secreta para firmar los tokens.
    | IMPORTANTE: Usar valores seguros en producción
    |
    */
    'secret' => env('JWT_SECRET', 'your-secret-key-change-this-in-production'),

    /*
    |--------------------------------------------------------------------------
    | Public Key (para RS256)
    |--------------------------------------------------------------------------
    */
    'public_key' => env('JWT_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Private Key (para RS256)
    |--------------------------------------------------------------------------
    */
    'private_key' => env('JWT_PRIVATE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | TTL (Time To Live)
    |--------------------------------------------------------------------------
    |
    | Tiempo de vida del token en minutos.
    | Default: 1 hora (60 minutos)
    |
    */
    'ttl' => env('JWT_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | Refresh TTL
    |--------------------------------------------------------------------------
    |
    | Tiempo permitido para renovar un token expirado, en minutos.
    | Default: 2 semanas (20160 minutos)
    |
    */
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),

    /*
    |--------------------------------------------------------------------------
    | Hash Algorithm
    |--------------------------------------------------------------------------
    |
    | Algoritmo de hash para firmar claims adicionales.
    |
    */
    'hash_algorithm' => 'sha256',

    /*
    |--------------------------------------------------------------------------
    | Supported Algorithms
    |--------------------------------------------------------------------------
    |
    | Los algoritmos que se soportan para validar tokens.
    |
    */
    'supported_algs' => [
        'HS256',
        'HS512',
        'RS256',
    ],

    /*
    |--------------------------------------------------------------------------
    | Verify Claims
    |--------------------------------------------------------------------------
    |
    | Verificar los claims del token durante validación.
    |
    */
    'verify_claims' => [
        'iss' => env('APP_URL'),
        'aud' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Required Claims
    |--------------------------------------------------------------------------
    |
    | Claims que deben estar presentes en cada token.
    |
    */
    'required_claims' => [
        'iss',
        'iat',
        'exp',
        'nbf',
        'jti',
    ],

    /*
    |--------------------------------------------------------------------------
    | Blacklist
    |--------------------------------------------------------------------------
    |
    | Habilitar blacklist para invalidar tokens al logout.
    |
    */
    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Blacklist Grace Period
    |--------------------------------------------------------------------------
    |
    | Período de gracia para la blacklist en segundos.
    |
    */
    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),

    /*
    |--------------------------------------------------------------------------
    | Show Black Listed Exception
    |--------------------------------------------------------------------------
    |
    | Mostrar excepción cuando el token está en la blacklist.
    |
    */
    'show_black_listed_exception' => env('JWT_SHOW_BLACKLIST_EXCEPTION', false),

    /*
    |--------------------------------------------------------------------------
    | Leeway
    |--------------------------------------------------------------------------
    |
    | Tiempo de tolerancia en segundos para la validación de tiempos.
    | Útil para sincronización de relojes entre servidores.
    |
    */
    'leeway' => env('JWT_LEEWAY', 0),

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    |
    | Especifica el proveedor de autenticación.
    |
    */
    'providers' => [
        'jwt' => \Tymon\JwtAuth\Providers\Auth\Illuminate::class,
        'key' => \Tymon\JwtAuth\Providers\Auth\Illuminate::class,
        'storage' => \Tymon\JwtAuth\Providers\Storage\Illuminate::class,
    ],
];
