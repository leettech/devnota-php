<?php

namespace NFSe\Tests;

use NFSe\NFSe;
use NFSe\DTO\IssueNFSeDTO;
use NFSe\Models\PaymentNfse;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use NFSe\Entities\FiscalProfile\RpsConfig;
use NFSe\Exceptions\IllegalStateException;
use NFSe\Entities\FiscalProfile\NFSeFiscal;
use NFSe\Entities\FiscalProfile\FiscalConfig;
use NFSe\Entities\FiscalProfile\ServicoConfig;
use NFSe\Models\PaymentNfse\PaymentNfseStatus;
use NFSe\Entities\FiscalProfile\PrestadorConfig;

class GenerateNFSeTest extends TestCase
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

    public function test_generate_nfse()
    {
        Http::fake(['*' => Http::response()]);

        $issueDto = new IssueNFSeDTO(
            gatewayPaymentId: '1234',
            price: '4051',
            paymentDate: now(),
            customer: $this->fakeNfseCustomer()
        );

        NFSe::generate($issueDto);

        $nfse = PaymentNfse::first();

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request) => $request->data()['rps']['identificacao']['numero'] == $issueDto->rps);
        $this->assertEquals(PaymentNfseStatus::Processing, $nfse->status);
        $this->assertEquals($issueDto->rps, $nfse->rps);
        $this->assertEquals($issueDto->gatewayPaymentId, $nfse->gateway_payment_id);
    }

    public function test_dont_duplicate_call_to_generate_nfse()
    {
        Http::fake(['*' => Http::response()]);
        $this->expectException(IllegalStateException::class);

        $nfse = PaymentNfse::factory()->create(['rps' => 1, 'status' => PaymentNfseStatus::Issued]);

        NFSe::generate($nfse->toIssue());

        Http::assertSentCount(0);
    }

    public function test_foo()
    {
        $factory = \NFSe\Models\PaymentNfse::factory();

        $this->assertInstanceOf(\NFSe\Database\Factories\PaymentNfseFactory::class, $factory);
    }

    public function test_dont_generate_nfse_for_older_payments()
    {
        Http::fake(['*' => Http::response()]);
        $this->expectException(IllegalStateException::class);
        $issueDto = new IssueNFSeDTO(
            gatewayPaymentId: '1234',
            price: '4051',
            paymentDate: now()->subMonth(),
            customer: $this->fakeNfseCustomer()
        );

        NFSe::generate($issueDto);

        Http::assertSentCount(0);
    }
}
