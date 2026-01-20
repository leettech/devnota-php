<?php

namespace Tests\NFSe;

use NFSe\NFSe;
use NFSe\Models\Payment;
use NFSe\Tests\TestCase;
use NFSe\Models\PaymentNfse;
use Illuminate\Support\Facades\Http;

class ConsultNFSeTest extends TestCase
{
    public function test_consult_nfse()
    {
        Http::fake([
            '*' => Http::response(),
        ]);

        $payment = Payment::factory()->create();
        PaymentNfse::factory()->toPayment($payment)->create();

        NFSe::consult($payment);

        Http::assertSentCount(1);
    }
}
