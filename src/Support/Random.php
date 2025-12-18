<?php

namespace NFSe\Support;

class Random
{
    public static function randomDigits(int $length): string
    {
        $digits = '';

        for ($i = 0; $i < $length; $i++) {
            $digits .= random_int(1, 9);
        }

        return $digits;
    }
}
