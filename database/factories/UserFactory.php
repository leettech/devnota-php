<?php

namespace NFSe\Database\Factories;

use NFSe\Tests\Fixtures\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'stripe_id' => 'cus_'.$this->faker->numberBetween(1000, 9999),
        ];
    }
}
