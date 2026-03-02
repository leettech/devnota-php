<?php

namespace NFSe;

use Carbon\Carbon;
use NFSe\Models\PaymentNfse;
use NFSe\Support\HasArrayGet;
use Illuminate\Contracts\Support\Arrayable;

class GenerateNFSeTemplate implements Arrayable
{
    use HasArrayGet;

    public readonly string $emittedAt;

    protected array $data;
    public readonly bool $isBusiness;
    public readonly float $amount;

    public function __construct(protected PaymentNfse $nfse, protected NFSeCustomer $customer)
    {
        $this->emittedAt = Carbon::now()->timezone('America/Recife')->format('Y-m-d\TH:i:s');
        $this->isBusiness = strtolower($customer->documentType) === 'cnpj' ? true : false;
        $this->amount = $this->nfse->price;
        $this->data = $this->template();
    }

    public function toArray()
    {
        return $this->data;
    }

    public function template()
    {
        return tap([
            'identificacao' => [
                'numero' => (int) $this->nfse->id,
                'serie' => config('nfse.config.rps.serie'),
                'tipo' => config('nfse.config.rps.tipo'),
            ],
            'data_emissao' => $this->emittedAt,
            'natureza_operacao' => config('nfse.config.fiscal.natureza_operacao'),
            'optante_simples_nacional' => config('nfse.config.fiscal.optante_simples_nacional'),
            'incentivador_cultural' => config('nfse.config.fiscal.incentivador_cultural'),
            'status' => config('nfse.config.fiscal.status'),

            'servico' => [
                'valores' => [
                    'valor_servicos' => $this->nfse->price,
                    'iss_retido' => config('nfse.config.servico.iss_retido'),
                    'aliquota' => config('nfse.config.servico.aliquota'),
                    'pis_cofins_retido' => $this->pixCofinsRetido(),
                    'valor_ir' => $this->valorIr(),
                    'valor_csll' => $this->valorCsll()
                ],
                'item_lista_servico' => config('nfse.config.servico.item_lista_servico'),
                'codigo_tributacao_municipio' => config('nfse.config.servico.codigo_tributacao_municipio'),
                'nbs' => config('nfse.config.servico.nbs'),
                'discriminacao' => config('nfse.config.servico.discriminacao'),
                'codigo_municipio' => config('nfse.config.servico.codigo_municipio'),
                'municipio_incidencia' => config('nfse.config.servico.municipio_incidencia'),
                'exigibilidade_iss' => config('nfse.config.servico.exigibilidade_iss'),
            ],

            'tomador' => [],
        ], function (array &$data): void {
            $this->buildCustomer($data);
        });
    }

    /**
     * IMPORTANT:
     * - Order matters
     * - Empty values MUST NOT be sent
     */
    private function buildCustomer(array &$data): void
    {
        $customer = $this->customer;

        if (! empty($customer->taxId)) {
            $data['tomador'][strtolower($customer->documentType)] = $customer->taxId;
        }

        if (! empty($customer->name)) {
            $data['tomador']['nome'] = $customer->name;
        }

        if (! empty($customer->address)) {
            $data['tomador']['endereco'] = $customer->address;
        }

        if (! empty($customer->addressNumber)) {
            $data['tomador']['numero'] = $customer->addressNumber;
        }

        if (! empty($customer->complement)) {
            $data['tomador']['complemento'] = $customer->complement;
        }

        if (! empty($customer->neighborhood)) {
            $data['tomador']['bairro'] = $customer->neighborhood;
        }

        if (! empty($customer->cityIbgeCode)) {
            $data['tomador']['codigo_municipio'] = $customer->cityIbgeCode;
        }

        if (! empty($customer->uf)) {
            $data['tomador']['uf'] = $customer->uf;
        }

        if (! empty($customer->zipcode)) {
            $data['tomador']['cep'] = $customer->zipcode;
        }

        if (! empty($customer->phone)) {
            $data['tomador']['telefone'] = $customer->phone;
        }

        if (! empty($customer->email)) {
            $data['tomador']['email'] = $customer->email;
        }
    }

    /**
     * Passar 3 apenas Para CNPJ igual ou maior que 215,10
     */
    private function pixCofinsRetido()
    {
        if ($this->isBusiness && $this->amount >= 215.10) {
            return 3;
        }
        return 0;
    }

    /**
     * apenas para CNPJ com valor igual ou maior que 680 (1.5%)
     */
    private function valorIr()
    {
        if ($this->isBusiness && $this->amount >= 680) {
            return round($this->amount * 0.015, 2);
        }
        return 0;
    }

    /**
     * apenas para CNPJ com valor igual ou maior que 215,10 (1%)
     */
    public function valorCsll()
    {
        if ($this->isBusiness && $this->amount >= 215.10) {
            return round($this->amount * 0.01, 2);
        }
        return 0;
    }
}
