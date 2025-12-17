<?php

namespace Tests\NFSe;

use NFSe\NFSe;
use NFSe\Tests\TestCase;
use NFSe\Models\PaymentNfse;
use Illuminate\Support\Facades\Http;

class ConsultNFSeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::fake([
            '*' => Http::response(),
        ]);
    }

    public function test_consult_nfse()
    {
        $nfse = PaymentNfse::factory()->create();

        NFSe::consult($nfse);

        Http::assertSentCount(1);
    }
}
