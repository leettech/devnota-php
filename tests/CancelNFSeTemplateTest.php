<?php

namespace Tests\NFSe;

use NFSe\NFSe;
use NFSe\Tests\TestCase;
use NFSe\CancelNFSeTemplate;
use NFSe\Models\PaymentNfse;
use NFSe\Entities\FiscalProfile\RpsConfig;
use NFSe\Entities\FiscalProfile\NFSeFiscal;
use NFSe\Entities\FiscalProfile\FiscalConfig;
use NFSe\Entities\FiscalProfile\ServicoConfig;
use NFSe\Entities\FiscalProfile\PrestadorConfig;

class CancelNFSeTemplateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        NFSe::configureFiscalDefaults(
            new NFSeFiscal(
                new RpsConfig(1, 1),
                new FiscalConfig(1, 2, 3, 4),
                new ServicoConfig('teste', '1', '4', 'teste', '2611606', '124125', 1, 123, 50.3),
                new PrestadorConfig('16694290000150', '4765745')
            )
        );
    }

    public function test_template()
    {
        $nfse = PaymentNfse::factory()->make(['rps' => 1, 'number' => '12345']);
        $template = new CancelNFSeTemplate($nfse);

        $this->assertEquals('RPS_1', $template->get('Pedido.InfPedidoCancelamento._attributes.Id'));
        $this->assertEquals('12345', $template->get('Pedido.InfPedidoCancelamento.IdentificacaoNfse.Numero'));
    }

    public function test_to_xml()
    {
        $nfse = PaymentNfse::factory()->make(['rps' => 1, 'number' => '12345']);

        $template = new CancelNFSeTemplate($nfse);

        $xml = <<<'XML'
<?xml version="1.0"?>
<CancelarNfseEnvio xmlns="http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd">
	<Pedido>
		<InfPedidoCancelamento xmlns="http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd" Id="RPS_1">
        <IdentificacaoNfse>
            <Numero>12345</Numero>
            <Cnpj>16694290000150</Cnpj>
            <InscricaoMunicipal>4765745</InscricaoMunicipal>
            <CodigoMunicipio>2611606</CodigoMunicipio>
        </IdentificacaoNfse>
        <CodigoCancelamento>1</CodigoCancelamento>
	    </InfPedidoCancelamento>
	</Pedido>
</CancelarNfseEnvio>
XML;

        $this->assertXmlStringEqualsXmlString($xml, $template->toXml());
    }
}
