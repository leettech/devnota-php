<?php

return [
    'route' => [
        'prefix' => 'nfse',
        'middleware' => [],
    ],
    'base_uri' => 'https://devnota.com.br/api',
    'token' => env('NFSE_TOKEN'),
    'environment' => 'developer',
];
