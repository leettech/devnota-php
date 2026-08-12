<?php

return [
    'route' => [
        'prefix' => 'nfse',
        'middleware' => [],
    ],
    'base_uri' => 'https://api.devnota.com.br',
    'token' => env('NFSE_TOKEN'),
    'environment' => env('NFSE_ENVIRONMENT', env('APP_ENV') === 'local' ? 'developer' : 'production'),
    'log_channel' => env('NFSE_LOG_CHANNEL', 'nfse'),
    'dolar_fallback_value' => 5.3,
    'retry_stuck_delay_in_minutes' => env('NFSE_RETRY_STUCK_DELAY_IN_MINUTES', 30),

    'models' => [
        'user' => null,
    ],
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
