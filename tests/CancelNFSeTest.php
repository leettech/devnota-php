<?php

namespace Tests\NFSe;

use NFSe\NFSe;
use NFSe\Models\Payment;
use NFSe\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class CancelNFSeTest extends TestCase
{
    public function test_cancel_nfse()
    {
        Http::fake([
            '*' => Http::response(),
        ]);

        $payment = Payment::factory()->create();
        $payment->createNfse();

        NFSe::cancel($payment);

        Http::assertSentCount(1);
    }
}
