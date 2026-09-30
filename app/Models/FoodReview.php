<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodReview extends Model
{
    protected $fillable = [
        'order_item_id',
        'user_id',
        'food_id',
        'vendor_id',
        'portion_rating',
        'value_rating',
        'accuracy_rating',
    ];

    protected function casts(): array
    {
        return [
            'portion_rating' => 'integer',
            'value_rating' => 'integer',
            'accuracy_rating' => 'integer',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}
