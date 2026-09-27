<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'role', 'is_active', 'accepting_orders', 'opening_time', 'closing_time'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function foods(): HasMany
    {
        return $this->hasMany(Food::class, 'vendor_id');
    }

    public function vendorOrderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'vendor_id');
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'accepting_orders' => 'boolean',
            'wallet_balance' => 'decimal:2',
        ];
    }

    public function isAcceptingOrders(): bool
    {
        if (! $this->accepting_orders) {
            return false;
        }

        if (! $this->opening_time || ! $this->closing_time) {
            return true;
        }

        $now = now()->format('H:i');
        $openingTime = substr((string) $this->opening_time, 0, 5);
        $closingTime = substr((string) $this->closing_time, 0, 5);

        return $openingTime <= $closingTime
            ? $now >= $openingTime && $now <= $closingTime
            : $now >= $openingTime || $now <= $closingTime;
    }
}
