<?php

namespace NFSe\Listeners\Cashier;

use NFSe\NFSe;
use Carbon\Carbon;
use NFSe\Models\Payment;
use Illuminate\Support\Arr;
use NFSe\Support\DolarHoje;
use Illuminate\Database\Eloquent\Model;

class HandleCashierWebhook
{
    public function handle($event)
    {
        if (! $this->isPaymentSucceeded($event)) {
            return;
        }

        $charge = $this->extractCharge($event);

        if (! $this->isValidCharge($charge)) {
            return;
        }

        $user = $this->resolveUser($charge);

        if (! $user || ! NFSe::canIssueNFSeFor($user->email)) {
            return;
        }

        $amount = Arr::get($charge, 'amount_captured');
        $currency = Arr::get($charge, 'currency');

        $price = $amount / 100;

        $payment = Payment::firstOrCreate([
            'gateway_payment_id' => Arr::get($charge, 'payment_intent'),
        ], [
            'user_id' => $user->getKey(),
            'date' => Carbon::createFromTimestamp(Arr::get($charge, 'created')),
            'price' => (string) DolarHoje::convertIf(str($currency)->upper()->toString() === 'USD', $price),
        ]);

        NFSe::generate($payment);
    }

    private function extractCharge($event)
    {
        if (Arr::get($event->payload, 'type') === 'payment_intent.succeeded') {
            return Arr::get($event->payload, 'data.object.charges.data.0');
        }

        return null;
    }

    public static function user(): ?string
    {
        $model = config('nfse.models.user');

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

    private function isPaymentSucceeded($event): bool
    {
        return Arr::get($event->payload, 'type') === 'payment_intent.succeeded';
    }

    private function isValidCharge(?array $charge): bool
    {
        if (is_null($charge)) {
            return false;
        }

        return Arr::get($charge, 'paid', false) === true;
    }

    private function resolveUser(array $charge): ?Model
    {
        $userModel = self::user();

        if (! $userModel) {
            return null;
        }

        $user = $userModel::query()
            ->where('stripe_id', Arr::get($charge, 'customer'))
            ->first();

        if (! $user) {
            nfseLogger()->info(
                'User not found. It is not possible to create a payment without billing information.',
                ['charge' => $charge]
            );
        }

        return $user;
    }
}
