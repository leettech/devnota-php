<?php

namespace NFSe\Listeners;

use NFSe\Events\RequestSent;
use NFSe\Models\NfseRequestLog;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SaveRequestLog implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(RequestSent $event)
    {
        NfseRequestLog::create([
            'url' => $event->url,
            'body' => $event->body,
            'method' => $event->method,
            'headers' => $event->headers,
            'response' => $event->response,
         ]);
    }
}
