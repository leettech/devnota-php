<?php

namespace NFSe;

final class NFSeConfig
{
    private static ?string $billingProvider = null;

    public static function useCashier(): void
    {
        self::$billingProvider = 'cashier';
    }

    public static function billingProvider(): ?string
    {
        return self::$billingProvider;
    }

    public static function isCashier(): bool
    {
        return self::$billingProvider === 'cashier';
    }
}
