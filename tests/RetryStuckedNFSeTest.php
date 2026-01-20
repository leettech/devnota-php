<?php

namespace Tests\NFSe;

use NFSe\NFSe;
use NFSe\Models\Payment;
use NFSe\Tests\TestCase;
use Illuminate\Support\Facades\Http;
use NFSe\Models\PaymentNfse;

class RetryStuckedNFSeTest extends TestCase
{
    public function test_retry_stucked_nfse()
    {
        Http::fake([
            '*' => Http::fakeSequence()->whenEmpty(Http::response()),
        ]);

        $payment = Payment::factory()->create();
        PaymentNfse::factory()->toPayment($payment)->create();


        NFSe::retryStucked($payment);

        Http::assertSentCount(2);
    }
}
