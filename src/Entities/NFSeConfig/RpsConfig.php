<?php

namespace NFSe\Entities\NFSeConfig;

final class RpsConfig
{
    public function __construct(
        public readonly int $serie,
        public readonly int $tipo,
    ) {}

    public static function setup(): self
    {
        return new self(
            serie: nfseConfigValue('rps.serie'),
            tipo: nfseConfigValue('rps.tipo'),
        );
    }
}
