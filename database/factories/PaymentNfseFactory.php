<?php

namespace NFSe\Database\Factories;

use NFSe\Models\Payment;
use NFSe\Models\PaymentNfse;
use NFSe\Models\PaymentNfse\PaymentNfseStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentNfseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentNfse::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rps' => $this->faker->numberBetween(1000, 9999),
            'payment_id' => Payment::factory(),
            'number' => $this->faker->numberBetween(1000, 9999),
            'price' => (string) $this->faker->randomNumber(4),
            'payment_date' => now(),
            'customer' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->safeEmail(),
            ],
        ];
    }

    public function issued()
    {
        return $this->state(function () {
            return [
                'status' => PaymentNfseStatus::Issued,
            ];
        });
    }

    public function toPayment(Payment $payment)
    {
        return $this->state(function () use ($payment) {
            return [
                'rps' => $payment->id,
                'payment_id' => $payment->id,
            ];
        });
    }
}
