<?php

namespace NFSe\DTO;

use Carbon\Carbon;
use NFSe\NFSeCustomer;
use NFSe\Support\Random;

final class IssueNFSeDTO
{
    public readonly string $rps;

    public function __construct(
        public string $gatewayPaymentId,
        public string $price,
        public Carbon $paymentDate,
        public NFSeCustomer $customer,
        ?string $rps = null
    ) {
        if (is_null($rps)) {
            $this->rps = Random::randomDigits(5);
        } else {
            $this->rps = $rps;
        }
    }
}
