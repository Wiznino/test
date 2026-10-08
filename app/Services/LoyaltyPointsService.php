<?php

namespace App\Services;

use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class LoyaltyPointsService
{
    public const int POINTS_PER_CEDI = 5;

    public function awardDailyLogin(User $user): bool
    {
        return DB::transaction(function () use ($user): bool {
            $customer = User::query()->lockForUpdate()->findOrFail($user->id);
            $reference = 'daily-login-'.$customer->id.'-'.now()->toDateString();

            if ($customer->loyaltyTransactions()->where('reference', $reference)->exists()) {
                return false;
            }

            $customer->increment('loyalty_points', 0.1);
            $customer->loyaltyTransactions()->create([
                'type' => 'daily_login',
                'points' => 0.1,
                'description' => 'Daily sign-in reward',
                'reference' => $reference,
            ]);

            return true;
        });
    }

    public static function pointsRequiredForMeal(float $price): int
    {
        return max(1, (int) ceil($price * self::POINTS_PER_CEDI));
    }

    public function awardForOrder(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->payment_status !== 'paid' || $lockedOrder->status === 'cancelled'
                || $lockedOrder->payment_method === 'loyalty' || $lockedOrder->loyalty_points_awarded > 0) {
                return;
            }

            $points = (int) $lockedOrder->items()->sum('quantity');

            if ($points < 1) {
                return;
            }

            $customer = User::query()->lockForUpdate()->findOrFail($lockedOrder->user_id);
            $customer->increment('loyalty_points', $points);
            $lockedOrder->update(['loyalty_points_awarded' => $points]);
            $customer->loyaltyTransactions()->create([
                'order_id' => $lockedOrder->id,
                'type' => 'earned',
                'points' => $points,
                'description' => 'Points earned from order #'.$lockedOrder->id,
                'reference' => 'order-'.$lockedOrder->id.'-earned',
            ]);

            $streak = $this->currentStreak($customer);
            foreach ([3 => 2, 7 => 5] as $milestone => $bonusPoints) {
                if ($streak !== $milestone) {
                    continue;
                }

                $reference = 'streak-'.$milestone.'-'.$customer->id.'-'.$lockedOrder->paid_at->toDateString();
                if (LoyaltyTransaction::where('reference', $reference)->exists()) {
                    continue;
                }

                $customer->increment('loyalty_points', $bonusPoints);
                $customer->loyaltyTransactions()->create([
                    'order_id' => $lockedOrder->id,
                    'type' => 'streak_bonus',
                    'points' => $bonusPoints,
                    'description' => $milestone.'-day meal streak bonus',
                    'reference' => $reference,
                ]);
            }
        });
    }

    public function reverseForOrder(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $points = (int) $lockedOrder->loyalty_points_awarded;
            $customer = User::query()->lockForUpdate()->findOrFail($lockedOrder->user_id);

            if ($points > 0) {
                $reference = 'order-'.$lockedOrder->id.'-reversed';
                if (! LoyaltyTransaction::where('reference', $reference)->exists()) {
                    $customer->decrement('loyalty_points', $points);
                    $customer->loyaltyTransactions()->create([
                        'order_id' => $lockedOrder->id,
                        'type' => 'reversed',
                        'points' => $points,
                        'description' => 'Points reversed for cancelled order #'.$lockedOrder->id,
                        'reference' => $reference,
                    ]);
                }
            }

            $redeemedPoints = (int) $lockedOrder->loyalty_points_redeemed;
            $redeemReference = 'order-'.$lockedOrder->id.'-redeemed-returned';
            if ($redeemedPoints > 0 && ! LoyaltyTransaction::where('reference', $redeemReference)->exists()) {
                $customer->increment('loyalty_points', $redeemedPoints);
                $customer->loyaltyTransactions()->create([
                    'order_id' => $lockedOrder->id,
                    'type' => 'returned',
                    'points' => $redeemedPoints,
                    'description' => 'Points returned for cancelled reward order #'.$lockedOrder->id,
                    'reference' => $redeemReference,
                ]);
            }

            $streakBonuses = LoyaltyTransaction::query()
                ->where('order_id', $lockedOrder->id)
                ->where('type', 'streak_bonus')
                ->get();

            foreach ($streakBonuses as $bonus) {
                $reference = $bonus->reference.'-reversed';
                if (LoyaltyTransaction::where('reference', $reference)->exists()) {
                    continue;
                }

                $customer->decrement('loyalty_points', $bonus->points);
                $customer->loyaltyTransactions()->create([
                    'order_id' => $lockedOrder->id,
                    'type' => 'reversed',
                    'points' => $bonus->points,
                    'description' => 'Streak bonus reversed for cancelled order #'.$lockedOrder->id,
                    'reference' => $reference,
                ]);
            }
        });
    }

    public function currentStreak(User $user): int
    {
        $days = Order::query()
            ->where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('payment_method', '!=', 'loyalty')
            ->where('paid_at', '>=', now()->subDays(365)->startOfDay())
            ->latest('paid_at')
            ->pluck('paid_at')
            ->map(fn ($paidAt): string => CarbonImmutable::parse($paidAt)->toDateString())
            ->unique()
            ->values();

        $today = now()->startOfDay();
        $firstDay = $days->first();

        if ($firstDay !== $today->toDateString() && $firstDay !== $today->copy()->subDay()->toDateString()) {
            return 0;
        }

        $expectedDay = $firstDay === $today->toDateString() ? $today : $today->copy()->subDay();
        $streak = 0;

        foreach ($days as $day) {
            if ($day !== $expectedDay->toDateString()) {
                break;
            }

            $streak++;
            $expectedDay->subDay();
        }

        return $streak;
    }
}
