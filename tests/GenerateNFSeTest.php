<?php

namespace NFSe\Tests;

use NFSe\NFSe;
use NFSe\Models\Payment;
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

        $payment = Payment::factory()->create();

        NFSe::generate($payment);

        $nfse = PaymentNfse::first();

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request) => $request->data()['rps']['identificacao']['numero'] == $payment->id);
        $this->assertEquals(PaymentNfseStatus::Processing, $nfse->status);
        $this->assertEquals($payment->id, $nfse->rps);
        $this->assertEquals($payment->gateway_payment_id, $nfse->gateway_payment_id);
    }

    public function test_dont_duplicate_call_to_generate_nfse()
    {
        Http::fake(['*' => Http::response()]);
        $this->expectException(IllegalStateException::class);

        $payment = Payment::factory()->create();
        PaymentNfse::factory()->issued()->toPayment($payment)->create();

        NFSe::generate($payment);

        Http::assertSentCount(0);
    }

    public function test_dont_generate_nfse_for_older_payments()
    {
        Http::fake(['*' => Http::response()]);
        $this->expectException(IllegalStateException::class);

        $payment = Payment::factory()->create(['date' => now()->subMonth()]);

        NFSe::generate($payment);

        Http::assertSentCount(0);

        $this->assertNull($payment->refresh()->paymentNfse);
    }

    public function test_dont_generate_nfse_for_zero_value_payments()
    {
        Http::fake(['*' => Http::response()]);
        $this->expectException(IllegalStateException::class);

        $payment = Payment::factory()->create(['price' => 0]);

        NFSe::generate($payment);

        Http::assertSentCount(0);
        $this->assertNull($payment->refresh()->paymentNfse);
    }
}
