<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['user_id', 'total', 'status', 'pickup_time', 'notes', 'payment_status', 'payment_reference', 'payment_method', 'paid_at', 'cancelled_at', 'cancelled_by', 'cancellation_reason', 'refund_status', 'refund_amount', 'refund_reference', 'refunded_at', 'refunded_by'];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'pickup_time' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refund_amount' => 'decimal:2',
            'refunded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }
}
