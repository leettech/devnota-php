<?php

namespace Tests\NFSe;

use NFSe\Tests\TestCase;
use NFSe\Models\PaymentNfse;
use NFSe\ConsultNFSeTemplate;

class ConsultNFSeTemplateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_template()
    {
        $payment = PaymentNfse::factory()->make(['id' => 142123]);
        $template = new ConsultNFSeTemplate($payment);

        $this->assertEquals(142123, $template->get('identificacao_rps.numero'));
    }

    public function test_to_array()
    {
        $payment = PaymentNfse::factory()->make(['id' => 142123]);
        $template = new ConsultNFSeTemplate($payment);

        $expected = [
            'identificacao_rps' => [
                'numero' => 142123,
                'serie' => 1,
                'tipo' => 1,
            ],
            'prestador' => [
                'cnpj' => '16694290000150',
                'inscricao_municipal' => '4765745',
            ],
        ];

        $this->assertEquals($expected, $template->toArray());
    }
}
