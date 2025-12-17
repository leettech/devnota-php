<?php

namespace NFSe\Entities\FiscalProfile;

final class RpsConfig
{
    public function __construct(
        public readonly int $serie,
        public readonly int $tipo,
    ) {}
}
