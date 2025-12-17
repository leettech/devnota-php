<?php

namespace NFSe\Models\PaymentNfse;

enum PaymentNfseStatus: string
{
    case Waiting = 'waiting';
    case Processing = 'processing';
    case Issued = 'issued';
    case Canceled = 'canceled';
    case Error = 'error';
}
