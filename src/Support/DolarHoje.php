<?php

namespace NFSe\Support;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class DolarHoje
{
    public static function convert($price)
    {
        $dolar = cache()->remember('dolar-hoje', 60 * 60 * 24, function () {
            return rescue(function () {
                /** @var Response $response */
                $response = Http::get('https://economia.awesomeapi.com.br/json/last/usd');
                
                if (!$response->successful()) {
                    return config('nfse.dolar_fallback_value');
                }

                return $response->json('USDBRL.low');
            }, config('nfse.dolar_fallback_value'));
        });

        /**
         * Double check
         */
        $dolar = $dolar ?? config('nfse.dolar_fallback_value');

        return round($price * $dolar, 2);
    }

    public static function convertIf(bool $condition, $price)
    {
        if ($condition) {
            return DolarHoje::convert($price);
        }

        return $price;
    }
}
