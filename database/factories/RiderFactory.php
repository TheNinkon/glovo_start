<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RiderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'status' => fake()->randomElement(['active', 'inactive', 'blocked']),
            'supervisor_id' => null,
        ];
    }
}
