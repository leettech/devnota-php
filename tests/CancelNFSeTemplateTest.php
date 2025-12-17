<?php

namespace Tests\NFSe;

use NFSe\Tests\TestCase;
use NFSe\CancelNFSeTemplate;
use NFSe\Models\PaymentNfse;

class CancelNFSeTemplateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
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
