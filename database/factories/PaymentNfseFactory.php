<?php

namespace NFSe\Database\Factories;

use NFSe\Models\PaymentNfse;
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
            'gateway_payment_id' => $this->faker->numberBetween(1000, 9999),
            'number' => $this->faker->numberBetween(1000, 9999),
            'price' => (string) $this->faker->randomNumber(4),
            'payment_date' => now(),
            'customer' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->safeEmail(),
            ],
        ];
    }
}
