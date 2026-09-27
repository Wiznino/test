<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WalletTransaction>
 */
class WalletTransactionFactory extends Factory
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
            'type' => 'credit',
            'amount' => fake()->randomFloat(2, 1, 500),
            'status' => 'completed',
            'reference' => (string) Str::uuid(),
            'description' => 'Wallet top up',
            'completed_at' => now(),
        ];
    }
}
