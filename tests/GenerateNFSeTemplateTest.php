<?php

namespace NFSe\Tests;

use NFSe\NFSeCustomer;
use Illuminate\Support\Arr;
use NFSe\Models\PaymentNfse;
use NFSe\GenerateNFSeTemplate;

class GenerateNFSeTemplateTest extends TestCase
{
    public function test_template()
    {
        $nfse = PaymentNfse::factory()->make([
            'customer' => $this->customerData()->toArray(),
        ]);
        $template = new GenerateNFSeTemplate($nfse);

        $this->assertEquals($nfse->rps, $template->get('identificacao.numero'));
        $this->assertEquals($nfse->price, $template->get('servico.valores.valor_servicos'));
        $this->assertEquals('Company ZeroOne', $template->get('tomador.nome'));
        $this->assertEquals('contact@zeroone.com', $template->get('tomador.email'));
        $this->assertEquals('049.611.720-30', $template->get('tomador.cpf'));
        $this->assertNull($template->get('tomador.endereco'));
        $this->assertNull($template->get('tomador.complemento'));
        $this->assertNull($template->get('tomador.cep'));
        $this->assertNull($template->get('tomador.telefone'));
    }

    public function test_template_business()
    {
        $nfse = PaymentNfse::factory()->make([
            'id' => 1,
            'customer' => $this->customerData(
                documentType: 'Cnpj',
                taxId: '30.479.485/0001-09'
            )->toArray(),
        ]);
        $template = new GenerateNFSeTemplate($nfse);

        $this->assertEquals('30.479.485/0001-09', $template->get('tomador.cnpj'));
    }

    public function test_template_address()
    {
        $nfse = PaymentNfse::factory()->make([
            'id' => 1,
            'customer' => $this->customerData(
                address: 'Rua dos Alfeneiros, 4'
            )->toArray(),
        ]);
        $template = new GenerateNFSeTemplate($nfse);

        $this->assertEquals('Rua dos Alfeneiros, 4', $template->get('tomador.endereco'));
    }

    public function test_template_complement()
    {
        $nfse = PaymentNfse::factory()->make([
            'id' => 1,
            'customer' => $this->customerData(
                complement: 'Casa'
            )->toArray(),
        ]);
        $template = new GenerateNFSeTemplate($nfse);

        $this->assertEquals('Casa', $template->get('tomador.complemento'));
    }

    public function test_template_zipcode()
    {
        $nfse = PaymentNfse::factory()->make([
            'id' => 1,
            'customer' => $this->customerData(
                zipcode: '55259000'
            )->toArray(),
        ]);
        $template = new GenerateNFSeTemplate($nfse);

        $this->assertEquals('55259000', $template->get('tomador.cep'));
    }

    public function test_template_phone()
    {
        $nfse = PaymentNfse::factory()->make([
            'id' => 1,
            'customer' => $this->customerData(
                phone: '87991923309'
            )->toArray(),
        ]);
        $template = new GenerateNFSeTemplate($nfse);

        $this->assertEquals('87991923309', $template->get('tomador.telefone'));
    }

    public function test_template_address_number()
    {
        $nfse = PaymentNfse::factory()->make([
            'id' => 1,
            'customer' => $this->customerData(
                addressNumber: 'n 4'
            )->toArray(),
        ]);
        $template = new GenerateNFSeTemplate($nfse);

        $this->assertEquals('n 4', $template->get('tomador.numero'));
    }

    public function test_template_neighborhood()
    {
        $nfse = PaymentNfse::factory()->make([
            'id' => 1,
            'customer' => $this->customerData(
                neighborhood: 'Boa vista'
            )->toArray(),
        ]);
        $template = new GenerateNFSeTemplate($nfse);

        $this->assertEquals('Boa vista', $template->get('tomador.bairro'));
    }

    public function test_empty_billing_info()
    {
        $nfse = PaymentNfse::factory()->make([
            'id' => 1,
            'customer' => [
                'email' => 'fake@email.com',
                'name' => 'fake name',
            ],
        ]);
        $template = new GenerateNFSeTemplate($nfse);

        $this->assertEquals('fake name', $template->get('tomador.nome'));
        $this->assertEquals('fake@email.com', $template->get('tomador.email'));
    }

    public function test_to_array()
    {
        $nfse = PaymentNfse::factory()->make([
            'rps' => 1,
            'customer' => $this->customerData(phone: '11982331122', address: 'Rua dos Alfenereos, 4')->toArray(),
        ]);
        $template = new GenerateNFSeTemplate($nfse);

        $expected = [
            'identificacao' => [
                'numero' => 1,
                'serie' => 1,
                'tipo' => 1,
            ],
            'data_emissao' => $template->emittedAt,
            'natureza_operacao' => 1,
            'optante_simples_nacional' => 3,
            'incentivador_cultural' => 2,
            'status' => 1,
            'servico' => [
                'valores' => [
                    'valor_servicos' => $nfse->price,
                    'iss_retido' => 2,
                    'aliquota' => 16.74,
                ],
                'item_lista_servico' => '010401',
                'codigo_tributacao_municipio' => '501',
                'nbs' => '115022000',
                'discriminacao' => 'TESTE DISC LEETTECH',
                'codigo_municipio' => '2611606',
                'municipio_incidencia' => '2611606',
                'exigibilidade_iss' => 1,
            ],
            'tomador' => [
                'cpf' => '049.611.720-30',
                'nome' => 'Company ZeroOne',
                'endereco' => 'Rua dos Alfenereos, 4',
                'codigo_municipio' => '2611606',
                'uf' => 'PE',
                'telefone' => '11982331122',
                'email' => 'contact@zeroone.com',
            ],
        ];

        $this->assertEquals($expected, $template->toArray());
    }

    public function test_to_array_address_complete()
    {
        $nfse = PaymentNfse::factory()->make([
            'id' => 1,
            'customer' => $this->customerData(
                address: 'Rua Jose Milton Lopz',
                addressNumber: '992',
                neighborhood: 'Zona Nova',
                complement: 'Apto 802',
                zipcode: '51021510',
                cityIbgeCode: '2611606',
                uf: 'PE'
            )->toArray(),
        ]);

        $template = new GenerateNFSeTemplate($nfse);

        $result = $template->toArray();
        $this->assertEquals([
            'endereco' => 'Rua Jose Milton Lopz',
            'numero' => '992',
            'complemento' => 'Apto 802',
            'bairro' => 'Zona Nova',
            'codigo_municipio' => '2611606',
            'uf' => 'PE',
            'cep' => '51021510',
            'cpf' => '049.611.720-30',
            'nome' => 'Company ZeroOne',
            'email' => 'contact@zeroone.com',
        ], $result['tomador']);
    }

    private function customerData(...$args): NFSeCustomer
    {
        return new NFSeCustomer(
            'Company ZeroOne',
            'contact@zeroone.com',
            Arr::get($args, 'phone'),
            Arr::get($args, 'zipcode'),
            Arr::get($args, 'address'),
            Arr::get($args, 'complement'),
            Arr::get($args, 'addressNumber'),
            Arr::get($args, 'neighborhood'),
            Arr::get($args, 'cityIbgeCode', '2611606'),
            Arr::get($args, 'uf', 'PE'),
            Arr::get($args, 'documentType', 'Cpf'),
            Arr::get($args, 'taxId', '049.611.720-30'),
        );
    }
}
