<?php

namespace Tests\NFSe;

use NFSe\NFSe;
use NFSe\Tests\TestCase;
use NFSe\Models\PaymentNfse;
use NFSe\ConsultNFSeTemplate;
use NFSe\Entities\FiscalProfile\RpsConfig;
use NFSe\Entities\FiscalProfile\NFSeFiscal;
use NFSe\Entities\FiscalProfile\FiscalConfig;
use NFSe\Entities\FiscalProfile\ServicoConfig;
use NFSe\Entities\FiscalProfile\PrestadorConfig;

class ConsultNFSeTemplateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        NFSe::configureFiscalDefaults(
            new NFSeFiscal(
                new RpsConfig(1, 1),
                new FiscalConfig(1, 2, 3, 4),
                new ServicoConfig('teste', '1', '4', 'teste', '124', '124125', 1, 123, 50.3),
                new PrestadorConfig('16694290000150', '4765745')
            )
        );
    }

    public function test_template()
    {
        $payment = PaymentNfse::factory()->make(['rps' => 142123]);
        $template = new ConsultNFSeTemplate($payment);

        $this->assertEquals(142123, $template->get('identificacao_rps.numero'));
    }

    public function test_to_array()
    {
        $payment = PaymentNfse::factory()->make(['rps' => 142123]);
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
