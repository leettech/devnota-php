<?php

namespace NFSe;

use NFSe\Models\PaymentNfse;
use NFSe\Support\HasArrayGet;
use Illuminate\Contracts\Support\Arrayable;

class ConsultNFSeTemplate implements Arrayable
{
    use HasArrayGet;

    protected array $data;

    public function __construct(protected PaymentNfse $nfse)
    {
        $this->data = self::template();
    }

    public static function create(PaymentNfse $nfse): self
    {
        return new self($nfse);
    }

    public function toArray()
    {
        return $this->data;
    }

    public function template()
    {
        return [
            'identificacao_rps' => [
                'numero' => $this->nfse->rps,
                'serie' => 1,
                'tipo' => 1,
            ],
            'prestador' => [
                'cnpj' => config('nfse.config.prestador.cnpj'),
                'inscricao_municipal' => config('nfse.config.prestador.inscricao_municipal'),
            ],
        ];
    }
}
