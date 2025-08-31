<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Rider;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'rider_id' => Rider::factory(),
            'account_id' => Account::factory(),
            'start_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'end_date' => fake()->optional(0.2)->dateTimeBetween('now', '+1 year'),
        ];
    }
}
