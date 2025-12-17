<?php

namespace NFSe\Entities\FiscalProfile;

class PrestadorConfig
{
    public function __construct(
        public readonly string $cnpj,
        public readonly string $inscricaoMunicipal,
    ) {}
}
