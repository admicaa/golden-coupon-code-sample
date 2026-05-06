<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | The application's primary user is the admin. The default guard stays on
    | `admin` (Passport-driven, Bearer-token) so unscoped Auth/auth() helpers
    | resolve the same user that the admin API contract expects.
    |
    */

    'defaults' => [
        'guard' => 'admin',
        'passwords' => 'admins',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | The `admin` guard is the only guard the API actually exercises in
    | production. Login validates credentials directly via the user provider
    | (see App\Http\Controllers\Backend\AuthController) and issues a Passport
    | personal access token — no session is involved. Multi-guard support is
    | now native to Passport, so `smartins/passport-multiauth` was removed.
    |
    */

    'guards' => [
        'admin' => [
            'driver' => 'passport',
            'provider' => 'admins',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'admins' => [
            'provider' => 'admins',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
