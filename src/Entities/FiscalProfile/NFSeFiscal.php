<?php

namespace NFSe\Entities\FiscalProfile;

final class NFSeFiscal
{
    public function __construct(
        public readonly RpsConfig $rps,
        public readonly FiscalConfig $fiscal,
        public readonly ServicoConfig $servico,
        public readonly PrestadorConfig $prestador
    ) {}
}
