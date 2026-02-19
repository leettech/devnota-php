<?php

namespace NFSe;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool generate(\NFSe\Models\Payment $payment)
 * @method static bool retryOnError(\NFSe\Models\Payment|\NFSe\Models\PaymentNfse $payment)
 *
 * @see NFSeService
 */
class NFSe extends Facade
{
    protected static $checkAllowIssueNFSeFor;

    protected static function getFacadeAccessor()
    {
        return 'nfse';
    }

    public static function canIssueNFSeFor($email): bool
    {
        if (is_callable(static::$checkAllowIssueNFSeFor)) {
            return call_user_func(static::$checkAllowIssueNFSeFor, $email);
        }

        return true;
    }

    public static function allowIssueNFSeFor(callable $callback)
    {
        static::$checkAllowIssueNFSeFor = $callback;
    }
}
