<?php

namespace NFSe;

use Illuminate\Contracts\Support\Arrayable;

class NFSeRequestPayload
{
    public static function make(Arrayable $nfseTemplate)
    {
        return [
            'ambiente' => config('nfse.environment'),
            'callback' => config('nfse.callback_route', null) ?? route('nfse.webhook.store'),
            'rps' => $nfseTemplate->toArray(),
        ];
    }
}
