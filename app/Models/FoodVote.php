<?php

namespace App\Models;

use Database\Factories\FoodVoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodVote extends Model
{
    /** @use HasFactory<FoodVoteFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'food_id', 'week_start'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }
}
