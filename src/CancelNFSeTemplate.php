<?php

namespace NFSe;

use NFSe\Support\HasArrayGet;
use NFSe\Models\PaymentNfse;
use Illuminate\Contracts\Support\Arrayable;

class CancelNFSeTemplate implements Arrayable
{
    use HasArrayGet;

    protected array $data;

    public function __construct(protected PaymentNfse $nfse)
    {
        $this->data = $this->template();
    }

    public function toArray()
    {
        return $this->data;
    }

    public function template()
    {
        return [
            'numero' => $this->nfse->verification_code, // yeah..
            'codigo_cancelamento' => '1',
            'motivo_cancelamento' => 'Cancelamento solicitado pelo cliente',
        ];
    }
}
