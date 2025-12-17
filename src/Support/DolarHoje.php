<?php

namespace NFSe\Support;

use Illuminate\Support\Facades\Http;

class DolarHoje
{
    public static function convert($price)
    {
        $dolar = cache()->remember('dolar-hoje', 60 * 60 * 24, function () {
            return rescue(function () {
                return Http::get('https://economia.awesomeapi.com.br/json/last/usd')->json('USDBRL.low');
            }, config('nfse.dolar_fallback_value')); // fallbackzinho de 5.30 não faz mal ;)
        });

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
