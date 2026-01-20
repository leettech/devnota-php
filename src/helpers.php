<?php

use NFSe\Support\NFSeLogger;

if (! function_exists('nfseLogger')) {
    function nfseLogger(): \Psr\Log\LoggerInterface
    {
        return NFSeLogger::log();
    }
}
