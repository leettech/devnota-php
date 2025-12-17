<?php

namespace NFSe\Entities\NFSeConfig;

class PrestadorConfig
{
    public function __construct(
        public readonly string $cnpj,
        public readonly string $inscricaoMunicipal,
    ) {}

    public static function setup(): self
    {
        return new self(
            cnpj: nfseConfigValue('prestador.cnpj'),
            inscricaoMunicipal: nfseConfigValue('prestador.inscricao_municipal'),
        );
    }
}
