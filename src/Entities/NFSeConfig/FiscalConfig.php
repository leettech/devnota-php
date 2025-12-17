<?php

namespace NFSe\Entities\NFSeConfig;

final class FiscalConfig
{
    public function __construct(
        public readonly int $naturezaOperacao,
        public readonly int $optanteSimplesNacional,
        public readonly int $incentivadorCultural,
        public readonly int $status,
    ) {}

    public static function setup(): self
    {
        return new self(
            naturezaOperacao: nfseConfigValue('fiscal.natureza_operacao'),
            optanteSimplesNacional: nfseConfigValue('fiscal.optante_simples_nacional'),
            incentivadorCultural: nfseConfigValue('fiscal.incentivador_cultural'),
            status: nfseConfigValue('fiscal.status'),
        );
    }
}
