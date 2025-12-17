<?php

namespace Tests\NFSe;

use NFSe\NFSe;
use NFSe\Tests\TestCase;
use NFSe\Models\PaymentNfse;
use Illuminate\Support\Facades\Http;

class CancelNFSeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::fake([
            '*' => Http::response(),
        ]);
    }

    public function test_cancel_nfse()
    {
        $nfse = PaymentNfse::factory()->create();

        NFSe::cancel($nfse);

        Http::assertSentCount(1);
    }
}
