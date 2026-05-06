<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Replaces the abandoned `spatie/laravel-cors` profile-based config with
    | the schema expected by Laravel's built-in `Illuminate\Http\Middleware\HandleCors`
    | middleware. Allowed origins continue to read from the `ALLOW_CORS` env
    | for backwards compatibility with existing deployments.
    |
    */

    'paths' => ['api/*', 'oauth/*'],

    'allowed_methods' => [
        'POST',
        'GET',
        'OPTIONS',
        'PUT',
        'PATCH',
        'DELETE',
    ],

    'allowed_origins' => array_values(array_filter(
        explode(',', (string) env('ALLOW_CORS', '*')),
        static fn ($origin) => $origin !== ''
    )),

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'X-Auth-Token',
        'Origin',
        'Authorization',
        'Content-Language',
        'X-Requested-With',
        'Accept',
    ],

    'exposed_headers' => [
        'Cache-Control',
        'Content-Language',
        'Content-Type',
        'Expires',
        'Last-Modified',
        'Pragma',
    ],

    'max_age' => 60 * 60 * 24,

    'supports_credentials' => false,
];
