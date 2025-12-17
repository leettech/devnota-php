<?php

namespace NFSe\Listeners\Cashier;

use NFSe\NFSe;
use Carbon\Carbon;
use NFSe\NFSeCustomer;
use NFSe\DTO\IssueNFSeDTO;
use Illuminate\Support\Arr;
use NFSe\Support\DolarHoje;

class HandleCashierWebhook
{
    public function handle($event)
    {
        if (Arr::get($event->payload, 'type') !== 'payment_intent.succeeded') {
            return;
        }

        $paymentId = Arr::get($event->payload, 'data.object.id');
        $billingDetails = Arr::get($event->payload, 'data.object.charges.data.0.billing_details');
        $amount = Arr::get($event->payload, 'data.object.amount');
        $currency = Arr::get($event->payload, 'data.object.currency');
        $paidAt = Carbon::createFromTimestamp(Arr::get($event->payload, 'data.object.charges.data.0.created'));

        $price = $amount / 100;

        $customer = NFSeCustomer::fromStripe($billingDetails);

        NFSe::generate(new IssueNFSeDTO(
            gatewayPaymentId: $paymentId,
            price: (string) DolarHoje::convertIf($currency === 'dolar', $price),
            paymentDate: $paidAt,
            customer: $customer
        ));
    }
}
