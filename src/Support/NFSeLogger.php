<?php

namespace NFSe\Support;

use Illuminate\Support\Facades\Log;

final class NFSeLogger
{
    public static function log(): \Psr\Log\LoggerInterface
    {
        $channel = config('nfse.log_channel');

        return config("logging.channels.$channel")
            ? Log::channel($channel)
            : Log::channel(config('logging.default'));
    }
}
