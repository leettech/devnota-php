<?php

namespace Tests\NFSe;

use NFSe\NFSe;
use NFSe\Models\Payment;
use NFSe\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class RetryStuckedNFSeTest extends TestCase
{
    public function test_retry_stucked_nfse()
    {
        Http::fake([
            '*' => Http::fakeSequence()->whenEmpty(Http::response()),
        ]);

        $payment = Payment::factory()->create();
        $payment->createNfse();

        NFSe::retryStucked($payment);

        Http::assertSentCount(2);
    }
}
