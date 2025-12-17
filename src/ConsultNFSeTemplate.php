<?php

namespace NFSe;

use NFSe\Models\PaymentNfse;
use NFSe\Support\HasArrayGet;
use Illuminate\Contracts\Support\Arrayable;
use NFSe\Entities\NFSeConfig\PrestadorConfig;

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
        $prestadorConfig = PrestadorConfig::setup();

        return [
            'identificacao_rps' => [
                'numero' => $this->nfse->rps,
                'serie' => 1,
                'tipo' => 1,
            ],
            'prestador' => [
                'cnpj' => $prestadorConfig->cnpj,
                'inscricao_municipal' => $prestadorConfig->inscricaoMunicipal,
            ],
        ];
    }
}
