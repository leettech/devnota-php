<?php

namespace NFSe\Support;

use Illuminate\Support\Facades\Http;

class ViaCepService
{
    public function consult(string $cep)
    {
        if (app()->environment('testing')) {
            return Http::response([]);
        }

        return Http::get("https://viacep.com.br/ws/$cep/json/");
    }
}
