<?php

namespace App\Services;

use App\Models\Food;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PickupSlotService
{
    public const int SLOT_MINUTES = 15;

    /**
     * @param  Collection<int, Food>  $foods
     * @return Collection<int, CarbonImmutable>
     */
    public function availableSlots(Collection $foods): Collection
    {
        $vendors = $foods->pluck('vendor')->filter()->unique('id')->values();
        $earliestPickup = CarbonImmutable::now()->addMinutes($foods->max('preparation_minutes') ?? 15);
        $roundedMinutes = (int) (ceil($earliestPickup->minute / self::SLOT_MINUTES) * self::SLOT_MINUTES);
        $firstSlot = $earliestPickup->setTime($earliestPickup->hour, 0)->addMinutes($roundedMinutes);
        $slots = collect(range(0, 15))->map(fn (int $offset): CarbonImmutable => $firstSlot->addMinutes($offset * self::SLOT_MINUTES));

        if ($vendors->isEmpty()) {
            return $slots;
        }

        $orderCounts = $this->orderCountsForSlots($vendors->pluck('id')->all(), $slots);

        return $slots->filter(function (CarbonImmutable $slot) use ($vendors, $orderCounts): bool {
            return $vendors->every(function (User $vendor) use ($slot, $orderCounts): bool {
                if (! $vendor->isOpenAt($slot)) {
                    return false;
                }

                $slotKey = $slot->format('Y-m-d H:i');
                $ordersInSlot = $orderCounts[$vendor->id][$slotKey] ?? 0;

                return $ordersInSlot < $vendor->max_orders_per_pickup_slot;
            });
        })->values();
    }

    public function isAvailable(Collection $foods, CarbonImmutable $requestedPickup): bool
    {
        return $this->availableSlots($foods)->contains(
            fn (CarbonImmutable $slot): bool => $slot->equalTo($requestedPickup),
        );
    }

    /**
     * @param  array<int, int>  $vendorIds
     * @param  Collection<int, CarbonImmutable>  $slots
     * @return array<int, array<string, int>>
     */
    private function orderCountsForSlots(array $vendorIds, Collection $slots): array
    {
        $slotValues = $slots->map(fn (CarbonImmutable $slot): string => $slot->format('Y-m-d H:i:s'));

        return OrderItem::query()
            ->select('order_items.vendor_id', 'orders.pickup_time', DB::raw('COUNT(DISTINCT order_items.order_id) as orders_count'))
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('order_items.vendor_id', $vendorIds)
            ->whereIn('orders.pickup_time', $slotValues)
            ->where('orders.status', '!=', 'cancelled')
            ->where(function ($query): void {
                $query->where('orders.payment_status', 'paid')
                    ->orWhere(function ($query): void {
                        $query->where('orders.payment_status', 'pending')
                            ->where('orders.created_at', '>=', now()->subMinutes(20));
                    });
            })
            ->groupBy('order_items.vendor_id', 'orders.pickup_time')
            ->get()
            ->reduce(function (array $counts, object $row): array {
                $slotKey = CarbonImmutable::parse($row->pickup_time)->format('Y-m-d H:i');
                $counts[$row->vendor_id][$slotKey] = (int) $row->orders_count;

                return $counts;
            }, []);
    }
}
