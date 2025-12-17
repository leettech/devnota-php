<?php

namespace NFSe\Entities\FiscalProfile;

final class ServicoConfig
{
    public function __construct(
        public readonly string $itemListaServico,
        public readonly string $codigoTributacaoMunicipio,
        public readonly string $nbs,
        public readonly string $discriminacao,
        public readonly string $codigoMunicipio,
        public readonly string $municipioIncidencia,
        public readonly int $exigibilidadeIss,
        public readonly int $issRetido,
        public readonly float $aliquota,
    ) {}
}
