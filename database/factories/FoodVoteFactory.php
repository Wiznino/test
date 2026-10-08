<?php

namespace Database\Factories;

use App\Models\Food;
use App\Models\FoodVote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<FoodVote>
 */
class FoodVoteFactory extends Factory
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
            'food_id' => Food::factory(),
            'week_start' => Carbon::now()->startOfWeek()->toDateString(),
        ];
    }
}
