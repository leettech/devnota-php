<?php

namespace NFSe\Support;

class StrHelper
{
    public static function clean(?string $text): string
    {
        $utf8 = [
            '/[áàâãªä]/u' => 'a',
            '/[ÁÀÂÃÄ]/u' => 'A',
            '/[ÍÌÎÏ]/u' => 'I',
            '/[íìîï]/u' => 'i',
            '/[éèêë]/u' => 'e',
            '/[ÉÈÊË]/u' => 'E',
            '/[óòôõºö]/u' => 'o',
            '/[ÓÒÔÕÖ]/u' => 'O',
            '/[úùûü]/u' => 'u',
            '/[ÚÙÛÜ]/u' => 'U',
            '/ç/' => 'c',
            '/Ç/' => 'C',
            '/ñ/' => 'n',
            '/Ñ/' => 'N',
            '/–/' => '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u' => ' ', // Literally a single quote
            '/[“”«»„]/u' => ' ', // Double quote
            '/ /' => ' ', // nonbreaking space (equiv. to 0x160)
        ];

        return preg_replace(array_keys($utf8), array_values($utf8), $text ?? '');
    }

    public static function onlyDigits(?string $value): ?string
    {
        return preg_replace('/[^0-9]/', '', $value ?? '');
    }

    public static function padPhone(?string $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if (strlen($value) > 11) {
            return substr($value, strlen($value) - 11, 11);
        }

        return str_pad($value, 11, '0', STR_PAD_LEFT);
    }
}
