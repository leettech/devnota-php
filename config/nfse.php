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

    'config' => [
        'rps' => [
            'serie' => null,
            'tipo' => null,
        ],

        'fiscal' => [
            'natureza_operacao' => null,
            'optante_simples_nacional' => null,
            'incentivador_cultural' => null,
            'status' => null,
        ],

        'servico' => [
            'item_lista_servico' => null,
            'codigo_tributacao_municipio' => null,
            'nbs' => null,
            'discriminacao' => null,
            'codigo_municipio' => null,
            'municipio_incidencia' => null,
            'exigibilidade_iss' => null,
            'iss_retido' => null,
            'aliquota' => null,
        ],

        'prestador' => [
            'cnpj' => null,
            'inscricao_municipal' => null,
        ],

    ],
];
