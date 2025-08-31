<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'courier_id' => 'GLV-' . $this->faker->unique()->numerify('########'),
            'status' => $this->faker->randomElement(['active', 'inactive', 'blocked']),
            'date_of_delivery' => $this->faker->dateTimeThisYear(),
        ];
    }
}
