<?php

namespace NFSe\Database\Factories;

use NFSe\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'gateway_payment_id' => $this->faker->numberBetween(1000, 9999),
            'price' => (string) $this->faker->randomNumber(4),
            'date' => now(),
            'customer' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->safeEmail(),
            ],
        ];
    }
}
