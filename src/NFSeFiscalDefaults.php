<?php

namespace NFSe;

use NFSe\Entities\NFSeConfig\NFSeFiscal;

class NFSeFiscalDefaults
{
    private static ?NFSeFiscal $profile = null;

    public static function set(NFSeFiscal $profile): void
    {
        self::$profile = $profile;
    }

    public static function profile(): NFSeFiscal
    {
        if (! self::$profile) {
            throw new \RuntimeException('NFSe fiscal profile not configured.');
        }

        return self::$profile;
    }
}
