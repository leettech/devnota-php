<?php

namespace NFSe\Entities\NFSeConfig;

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

    public static function setup()
    {
        return new self(
            itemListaServico: nfseConfigValue('servico.item_lista_servico'),
            codigoTributacaoMunicipio: nfseConfigValue('servico.codigo_tributacao_municipio'),
            nbs: nfseConfigValue('servico.nbs'),
            discriminacao: nfseConfigValue('servico.discriminacao'),
            codigoMunicipio: nfseConfigValue('servico.codigo_municipio'),
            municipioIncidencia: nfseConfigValue('servico.municipio_incidencia'),
            exigibilidadeIss: nfseConfigValue('servico.exigibilidade_iss'),
            issRetido: nfseConfigValue('servico.iss_retido'),
            aliquota: (float) nfseConfigValue('servico.aliquota'),
        );
    }
}
