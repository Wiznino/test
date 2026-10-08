<?php

namespace App\Models;

use Database\Factories\LoyaltyTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    /** @use HasFactory<LoyaltyTransactionFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'order_id', 'type', 'points', 'description', 'reference'];

    protected function casts(): array
    {
        return [
            'points' => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
