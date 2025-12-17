<?php

namespace Tests\NFSe;

use NFSe\NFSe;
use NFSe\Tests\TestCase;
use NFSe\Models\PaymentNfse;
use Illuminate\Support\Facades\Http;

class RetryStuckedNFSeTest extends TestCase
{
    public function test_retry_stucked_nfse()
    {
        Http::fake([
            '*' => Http::fakeSequence()->whenEmpty(Http::response()),
        ]);

        $nfse = PaymentNfse::factory()->make();

        NFSe::retryStucked($nfse);

        Http::assertSentCount(2);
    }
}
