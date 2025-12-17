<?php

namespace NFSe;

use Illuminate\Contracts\Events\Dispatcher;
use NFSe\Listeners\Cashier\HandleCashierWebhook;

trait EventMap
{
    protected function registerEvents(): void
    {
        $events = $this->app->make(Dispatcher::class);

        foreach ($this->eventsForProvider() as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }
    }

    protected function eventsForProvider(): array
    {
        // if (NFSeConfig::isCashier()) {
        //     return [
        //         'Laravel\\Cashier\\Events\\WebhookReceived' => [
        //             HandleCashierWebhook::class,
        //         ],
        //     ];
        // }

        return [];
    }
}
