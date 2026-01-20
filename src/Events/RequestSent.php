<?php

namespace NFSe\Events;

use Illuminate\Foundation\Events\Dispatchable;

class RequestSent
{
    use Dispatchable;

    public function __construct(
        public readonly string $url,
        public readonly string $method,
        public readonly array $headers,
        public readonly array $body,
        public readonly array $response,
    ) {}
}
