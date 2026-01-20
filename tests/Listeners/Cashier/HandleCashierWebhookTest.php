<?php

namespace Tests\NFSe\Listeners\Cashier;

use NFSe\NFSe;
use NFSe\Tests\TestCase;
use NFSe\Listeners\Cashier\HandleCashierWebhook;

class HandleCashierWebhookTest extends TestCase
{
    public function test_generate_nfse_if_pi_succeceds()
    {
        NFSe::shouldReceive('generate')
            ->once();

        $event = (object) [
            'payload' => $this->paymentIntentSucceededPayload(),
        ];

        $listener = new HandleCashierWebhook;
        $listener->handle($event);
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
        return json_decode(
            file_get_contents(__DIR__.'/fixtures/payment_intent.succeeded.json'),
            true,
            flags: JSON_THROW_ON_ERROR
        );
    }
}
