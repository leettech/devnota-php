<?php

namespace NFSe;

use Illuminate\Contracts\Support\Arrayable;

class NFSeRequestPayload
{
    public static function make(Arrayable $nfseTemplate)
    {
        return [
            'ambiente' => config('nfse.environment'), // todo: fix environment spelling
            'callback' => route('nfse.webhook.store'),
            'rps' => $nfseTemplate->toArray(),
        ];
    }
}
