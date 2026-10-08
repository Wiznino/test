<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'role', 'is_active', 'accepting_orders', 'opening_time', 'closing_time', 'max_orders_per_pickup_slot'])]
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

    public function favoriteFoods(): BelongsToMany
    {
        return $this->belongsToMany(Food::class, 'food_favorites')->withTimestamps();
    }

    public function vendorOrderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'vendor_id');
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function foodVotes(): HasMany
    {
        return $this->hasMany(FoodVote::class);
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
            'loyalty_points' => 'decimal:1',
            'max_orders_per_pickup_slot' => 'integer',
        ];
    }

    public function isAcceptingOrders(): bool
    {
        return $this->accepting_orders && $this->isOpenAt(now());
    }

    public function isOpenAt(CarbonInterface $dateTime): bool
    {
        if (! $this->opening_time || ! $this->closing_time) {
            return true;
        }

        $time = $dateTime->format('H:i');
        $openingTime = substr((string) $this->opening_time, 0, 5);
        $closingTime = substr((string) $this->closing_time, 0, 5);

        return $openingTime <= $closingTime
            ? $time >= $openingTime && $time <= $closingTime
            : $time >= $openingTime || $time <= $closingTime;
    }
}
