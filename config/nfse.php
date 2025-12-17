<?php

return [
    'route' => [
        'prefix' => 'nfse',
        'middleware' => [],
    ],
    'base_uri' => 'https://devnota.com.br/api',
    'token' => env('NFSE_TOKEN'),
    'environment' => env('NFSE_ENVIRONMENT', env('APP_ENV') === 'local' ? 'developer' : 'production'),
    'log_channel' => env('NFSE_LOG_CHANNEL', 'nfse'),
    'dolar_fallback_value' => 5.3,
    'callback_route' => env('NFSE_CALLBACK_URL'),
];
