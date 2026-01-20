<?php

namespace NFSe\Listeners;

use NFSe\Models\NfseWebhookLog;
use NFSe\Events\WebhookReceived;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SaveWebhookLog implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(WebhookReceived $event)
    {
        NfseWebhookLog::create([
            'request_data' => $event->requestData,
        ]);
    }
}
