<?php

namespace Database\Factories;

use App\Models\LoyaltyTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LoyaltyTransaction>
 */
class LoyaltyTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_id' => null,
            'type' => 'earned',
            'points' => fake()->numberBetween(1, 5),
            'description' => fake()->sentence(),
            'reference' => Str::uuid()->toString(),
        ];
    }
}
