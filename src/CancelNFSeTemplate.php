<?php

namespace NFSe;

use NFSe\Models\PaymentNfse;
use NFSe\Support\HasArrayGet;
use Spatie\ArrayToXml\ArrayToXml;
use Illuminate\Contracts\Support\Arrayable;

class CancelNFSeTemplate implements Arrayable
{
    use HasArrayGet;

    protected array $data;

    public function __construct(protected PaymentNfse $nfse)
    {
        $this->data = $this->template();
    }

    public static function create(PaymentNfse $nfse): self
    {
        return new self($nfse);
    }

    public function toXml(): string
    {
        return ArrayToXml::convert($this->toArray(), [
            'rootElementName' => 'CancelarNfseEnvio',
            '_attributes' => [
                'xmlns' => 'http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd',
            ],
        ]);
    }

    public function toSimpleXMLElement(): \SimpleXMLElement
    {
        return simplexml_load_string($this->toXml());
    }

    public function toArray()
    {
        return $this->data;
    }

    public function template()
    {
        $prestadorConfig = NFSeFiscalDefaults::profile()->prestador;
        $servicoConfig = NFSeFiscalDefaults::profile()->servico;

        return [
            'Pedido' => [
                'InfPedidoCancelamento' => [
                    '_attributes' => [
                        'xmlns' => 'http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd',
                        'Id' => sprintf('RPS_%s', $this->nfse->rps),
                    ],
                    'IdentificacaoNfse' => [
                        'Numero' => $this->nfse->number,
                        'Cnpj' => $prestadorConfig->cnpj,
                        'InscricaoMunicipal' => $prestadorConfig->inscricaoMunicipal,
                        'CodigoMunicipio' => $servicoConfig->codigoMunicipio,
                    ],
                    'CodigoCancelamento' => 1,
                ],
            ],
        ];
    }
}
