<?php

namespace NFSe\Tests\Console;

use NFSe\Models\Payment;
use NFSe\Tests\TestCase;
use NFSe\Models\PaymentNfse;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use NFSe\Models\PaymentNfse\PaymentNfseStatus;

class ProcessStuckedNFSeCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(['*' => Http::response()]);

        config()->set('nfse.retry_stuck_delay_in_minutes', 30);
    }

    public function test_it_reprocess_stucked_nfse()
    {
        $nfse = $this->createNfse(['updated_at' => now()->subHour()]);

        $this->artisan('nfse:process')->assertSuccessful();

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request) => $request->data()['rps']['identificacao']['numero'] == $nfse->id);
    }

    public function test_it_ignores_recently_updated_nfse()
    {
        $this->createNfse(['updated_at' => now()->subMinutes(5)]);

        $this->artisan('nfse:process')->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_it_ignores_nfse_that_are_not_processing()
    {
        $this->createNfse([
            'status' => PaymentNfseStatus::Issued,
            'updated_at' => now()->subHour(),
        ]);

        $this->artisan('nfse:process')->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_it_ignores_nfse_from_previous_months()
    {
        $this->createNfse([
            'created_at' => now()->subMonth(),
            'updated_at' => now()->subMonth(),
        ]);

        $this->artisan('nfse:process')->assertSuccessful();

        Http::assertNothingSent();
    }

    private function createNfse(array $attributes = []): PaymentNfse
    {
        $payment = Payment::factory()->create();

        $nfse = PaymentNfse::factory()->toPayment($payment)->create([
            'customer' => $this->fakeNfseCustomer(),
        ]);

        if ($attributes !== []) {
            // timestamps are overwritten on save, so update them through the query builder
            PaymentNfse::withoutEvents(fn () => PaymentNfse::query()
                ->whereKey($nfse->getKey())
                ->toBase()
                ->update($attributes));
        }

        return $nfse->refresh();
    }
}
