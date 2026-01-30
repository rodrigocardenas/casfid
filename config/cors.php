<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Configuration
    |--------------------------------------------------------------------------
    |
    | The values given here control the execution of the CORS middleware. By
    | default we allow '*' which means allow any origin. If you change this,
    | make sure to handle it appropriately.
    |
    */

    'paths' => ['api/*', 'login', 'register'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
