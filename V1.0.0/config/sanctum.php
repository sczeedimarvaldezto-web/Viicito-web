<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Sanctum expects your frontend and backend to be co-located or served
    | from the same domain. This array lists those domains that should be
    | considered "stateful" for cookie-based authentication.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,localhost:3001,localhost:8000,127.0.0.1,127.0.0.1:8000,127.0.0.1:3000,127.0.0.1:3001,',
        env('APP_URL') ? parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | This array contains the authentication guards that will be checked when
    | Sanctum is trying to authenticate a request.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. If this value is null, personal access tokens do
    | not expire.
    |
    */

    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | When authenticating your first-party SPA with Sanctum, you may need to
    | customize some of the middleware Sanctum uses while processing requests.
    |
    */

    'middleware' => [
        'verify_csrf_token' => \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => \Illuminate\Cookie\Middleware\EncryptCookies::class,
    ],

];
