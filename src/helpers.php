<?php

use NFSe\Support\NFSeLogger;
use NFSe\Exceptions\MissingFiscalConfigException;

if (! function_exists('nfseLogger')) {
    function nfseLogger(): \Psr\Log\LoggerInterface
    {
        return NFSeLogger::log();
    }
}

if (! function_exists('nfseConfigValue')) {
    function nfseConfigValue($field): mixed
    {
        $value = config("nfse.config.{$field}");

        if (is_null($value)) {
            throw MissingFiscalConfigException::missing($field);
        }

        return $value;
    }
}
