<?php

namespace Tests\NFSe\Listeners\Cashier;

use NFSe\NFSe;
use NFSe\Tests\TestCase;
use NFSe\Tests\Fixtures\User;
use NFSe\Listeners\Cashier\HandleCashierWebhook;

class HandleCashierWebhookTest extends TestCase
{
    public function test_generate_nfse_if_pi_succeceds()
    {
        NFSe::shouldReceive('generate')
            ->once();

        $payload = $this->paymentIntentSucceededPayload();
        $customerId = data_get($payload, 'data.object.charges.data.0.customer');

        $user = User::factory()->create(['stripe_id' => $customerId]);

        $listener = new HandleCashierWebhook;
        $listener->handle((object) [
            'payload' => $this->paymentIntentSucceededPayload(),
        ]);

        $payment = $user->payments()->first();

        $this->assertNotNull($payment);

        $this->assertEquals(data_get($payload, 'data.object.id'), $payment->gateway_payment_id);
    }

    public function test_ignore_not_chargeable_events()
    {
        NFSe::shouldReceive('generate')->never();

        $event = (object) [
            'payload' => [
                'type' => 'customer.created',
            ],
        ];

        (new HandleCashierWebhook)->handle($event);
    }

    public function test_not_generate_if_pi_not_paid()
    {
        NFSe::shouldReceive('generate')->never();

        $payload = $this->paymentIntentSucceededPayload();
        data_set($payload, 'data.object.charges.data.0.paid', false);

        $event = (object) [
            'payload' => $payload,
        ];

        (new HandleCashierWebhook)->handle($event);
    }

    private function paymentIntentSucceededPayload(): array
    {
        $payload = json_decode(
            file_get_contents(__DIR__.'/fixtures/payment_intent.succeeded.json'),
            true,
            flags: JSON_THROW_ON_ERROR
        );

        $now = now()->timestamp;
        data_set($payload, 'data.object.created', $now);
        data_set($payload, 'data.object.charges.data.0.created', $now);

        return $payload;
    }
}
