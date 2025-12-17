<?php

namespace NFSe\Exceptions;

final class MissingFiscalConfigException extends \Exception
{
    public static function missing(string $field): self
    {
        return new self(
            sprintf('NFSe fiscal configuration missing required field: "%s"', $field)
        );
    }
}
