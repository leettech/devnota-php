<?php

namespace NFSe\Models\PaymentNfse;

use NFSe\Models\PaymentNfse;
use NFSe\NFSeFiscalDefaults;

final class NFSePayload
{
    public function __construct(
        private PaymentNfse $nfse,
        private string $emittedAt,
    ) {}

    public function toArray(): array
    {
        $fiscalProfile = NFSeFiscalDefaults::profile();

        $rpsConfig = $fiscalProfile->rps;
        $fiscalConfig = $fiscalProfile->fiscal;
        $serviceConfig = $fiscalProfile->servico;

        return tap([
            'identificacao' => [
                'numero' => $this->nfse->rps,
                'serie' => $rpsConfig->serie,
                'tipo' => $rpsConfig->tipo,
            ],
            'data_emissao' => $this->emittedAt,
            'natureza_operacao' => $fiscalConfig->naturezaOperacao,
            'optante_simples_nacional' => $fiscalConfig->optanteSimplesNacional,
            'incentivador_cultural' => $fiscalConfig->incentivadorCultural,
            'status' => $fiscalConfig->status,

            'servico' => [
                'valores' => [
                    'valor_servicos' => $this->nfse->price,
                    'iss_retido' => $serviceConfig->issRetido,
                    'aliquota' => $serviceConfig->aliquota,
                ],
                'item_lista_servico' => $serviceConfig->itemListaServico,
                'codigo_tributacao_municipio' => $serviceConfig->codigoTributacaoMunicipio,
                'nbs' => $serviceConfig->nbs,
                'discriminacao' => $serviceConfig->discriminacao,
                'codigo_municipio' => $serviceConfig->codigoMunicipio,
                'municipio_incidencia' => $serviceConfig->municipioIncidencia,
                'exigibilidade_iss' => $serviceConfig->exigibilidadeIss,
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
        $customer = $this->nfse->customer;

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
}
