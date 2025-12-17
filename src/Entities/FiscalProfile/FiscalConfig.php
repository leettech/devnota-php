<?php

namespace NFSe\Entities\FiscalProfile;

final class FiscalConfig
{
    public function __construct(
        public readonly int $naturezaOperacao,
        public readonly int $optanteSimplesNacional,
        public readonly int $incentivadorCultural,
        public readonly int $status,
    ) {}
}
