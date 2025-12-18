<?php

namespace NFSe\Listeners\Cashier;

use NFSe\NFSe;
use Carbon\Carbon;
use NFSe\NFSeCustomer;
use NFSe\DTO\IssueNFSeDTO;
use Illuminate\Support\Arr;
use NFSe\Support\DolarHoje;
use Illuminate\Database\Eloquent\Model;

class HandleCashierWebhook
{
    public function handle($event)
    {
        if (! in_array(Arr::get($event->payload, 'type', ''), ['payment_intent.succeeded', 'charge.succeeded'])) {
            return;
        }

        $charge = $this->getCharge($event);

        if (is_null($charge)) {
            return;
        }

        if (! Arr::get($charge, 'paid', false)) {
            return;
        }

        $paymentId = Arr::get($charge, 'payment_intent');
        $billingDetails = $this->fillBilling($charge);

        if (! is_array($billingDetails)) {
            nfseLogger()->info('Payment without billing information', ['charge' => $charge]);

            return;
        }
        $amount = Arr::get($charge, 'amount_captured');
        $currency = Arr::get($charge, 'currency');
        $paidAt = Carbon::createFromTimestamp(Arr::get($charge, 'created'));

        $price = $amount / 100;

        $customer = NFSeCustomer::fromStripe($billingDetails);

        NFSe::generate(new IssueNFSeDTO(
            gatewayPaymentId: $paymentId,
            price: (string) DolarHoje::convertIf($currency === 'USD', $price),
            paymentDate: $paidAt,
            customer: $customer
        ));
    }

    private function getCharge($event)
    {
        if (Arr::get($event->payload, 'type') === 'payment_intent.succeeded') {
            return Arr::get($event->payload, 'data.object.charges.data.0');
        } elseif (Arr::get($event->payload, 'type') === 'charge.succeeded') {
            return Arr::get($event->payload, 'data.object');
        }

        return null;
    }

    // TODO: Achar estrutura mnelhor
    // muito acoplado com o linklist
    public static function user(): ?string
    {
        $model = config('nfse.models.payment');

        if (! is_string($model) || ! class_exists($model)) {
            nfseLogger()->info('You must configure nfse.models.user with a valid Eloquent model.');

            return null;
        }

        if (! is_subclass_of($model, Model::class)) {
            nfseLogger()->info("{$model} must extend ".Model::class);

            return null;
        }

        return $model;
    }

    private function fillBilling($charge)
    {
        $billingDetails = Arr::get($charge, 'billing_details');

        if (is_null(Arr::get($billingDetails, 'email'))) {
            $user = self::user();

            if (is_null($user)) {
                return;
            }
            $email = $user::query()->where('stripe_id', Arr::get($charge, 'customer'))->first()?->email;

            if (is_null($email)) {
                return;
            }

            $billingDetails['email'] = $email;
        }

        return $billingDetails;
    }
}
