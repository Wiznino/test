<?php

namespace App\Models;

use Database\Factories\WalletTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = ['user_id', 'type', 'amount', 'status', 'reference', 'description', 'completed_at'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'completed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @use HasFactory<WalletTransactionFactory> */
    use HasFactory;
}
