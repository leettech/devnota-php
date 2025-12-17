<?php

namespace NFSe\Tests;

use NFSe\NFSe;
use NFSe\DTO\IssueNFSeDTO;
use NFSe\Models\PaymentNfse;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use NFSe\Exceptions\IllegalStateException;
use NFSe\Models\PaymentNfse\PaymentNfseStatus;

class GenerateNFSeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
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
