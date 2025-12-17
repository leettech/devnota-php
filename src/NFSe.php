<?php

namespace NFSe;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void configureFiscalDefaults(\NFSe\Entities\FiscalProfile\NFSeFiscal $data)
 * @method static bool generate(\NFSe\DTO\IssueNFSeDTO $issueDto)
 * @method static bool retryOnError(\NFSe\Models\PaymentNfse $nfse)
 * @method static bool consult(\NFSe\Models\PaymentNfse $nfse)
 * @method static bool cancel(\NFSe\Models\PaymentNfse $nfse)
 * @method static bool retryStucked(\NFSe\Models\PaymentNfse $nfse)
 *
 * @see NFSeService
 */
class NFSe extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'nfse';
    }

    public static function configureToCashier(): void
    {
        NFSeConfig::useCashier();
    }
}
