<?php

namespace App\Http\Controllers\Customer;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\OrderUpdateNotification;
use App\Services\LoyaltyPointsService;
use App\Services\PickupSlotService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoyaltyController extends Controller
{
    public function index(Request $request, LoyaltyPointsService $loyaltyPoints, PickupSlotService $pickupSlotService): View
    {
        $user = $request->user();
        $weekStart = CarbonImmutable::now()->startOfWeek()->toDateString();
        $foods = Food::query()
            ->where('available', true)
            ->with('vendor')
            ->withCount(['votes' => fn ($query) => $query->whereDate('week_start', $weekStart)])
            ->orderBy('name')
            ->get()
            ->filter(fn (Food $food): bool => ! $food->vendor || $food->vendor->isAcceptingOrders())
            ->values();
        $currentVote = $user->foodVotes()->whereDate('week_start', $weekStart)->first();
        $transactions = $user->loyaltyTransactions()->latest()->paginate(10);
        $streak = $loyaltyPoints->currentStreak($user);
        $pickupSlotsByFood = $foods->mapWithKeys(fn (Food $food): array => [
            $food->id => $pickupSlotService->availableSlots(collect([$food])),
        ]);

        return view('loyalty.index', compact('foods', 'currentVote', 'transactions', 'streak', 'pickupSlotsByFood'));
    }

    public function vote(Request $request): RedirectResponse
    {
        $data = $request->validate(['food_id' => ['required', 'integer', 'exists:foods,id']]);
        $food = Food::query()->with('vendor')->findOrFail($data['food_id']);
        abort_unless($food->available && (! $food->vendor || $food->vendor->isAcceptingOrders()), 422, 'This meal is not currently available for voting.');

        $weekStart = CarbonImmutable::now()->startOfWeek()->toDateString();
        $voteCreated = DB::transaction(function () use ($request, $food, $weekStart): bool {
            $customer = $request->user()->newQuery()->lockForUpdate()->findOrFail($request->user()->id);

            if ($customer->foodVotes()->whereDate('week_start', $weekStart)->exists()) {
                return false;
            }

            $customer->foodVotes()->create([
                'food_id' => $food->id,
                'week_start' => $weekStart,
            ]);

            return true;
        });

        if (! $voteCreated) {
            return back()->withErrors(['food_id' => 'You have already voted this week. Come back next week to vote again.']);
        }

        return back()->with('success', 'Your vote is recorded. You can vote again next week.');
    }

    public function redeem(Request $request, PickupSlotService $pickupSlotService): RedirectResponse
    {
        $data = $request->validate([
            'food_id' => ['required', 'integer', 'exists:foods,id'],
            'pickup_time' => ['required', 'date_format:Y-m-d\\TH:i'],
        ]);
        $requestedPickup = CarbonImmutable::createFromFormat('Y-m-d\\TH:i', $data['pickup_time'], config('app.timezone'));

        $order = DB::transaction(function () use ($request, $data, $requestedPickup, $pickupSlotService): Order {
            $customer = $request->user()->newQuery()->lockForUpdate()->findOrFail($request->user()->id);
            $food = Food::query()->with('vendor')->lockForUpdate()->findOrFail($data['food_id']);
            if (! $food->available || ($food->vendor && ! $food->vendor->isAcceptingOrders())) {
                throw ValidationException::withMessages(['food_id' => 'This meal is no longer available. Choose another meal.']);
            }

            $pointsRequired = LoyaltyPointsService::pointsRequiredForMeal((float) $food->price);
            if ($customer->loyalty_points < $pointsRequired) {
                throw ValidationException::withMessages(['loyalty_points' => 'You need '.$pointsRequired.' points to purchase this meal.']);
            }

            $foods = collect([$food]);
            if (! $pickupSlotService->isAvailable($foods, $requestedPickup)) {
                throw ValidationException::withMessages(['pickup_time' => 'That pickup time is unavailable. Choose another slot.']);
            }

            $order = Order::create([
                'user_id' => $customer->id,
                'total' => $food->price,
                'status' => 'received',
                'payment_status' => 'paid',
                'payment_method' => 'loyalty',
                'paid_at' => now(),
                'pickup_time' => $requestedPickup,
                'notes' => 'Purchased with '.$pointsRequired.' ATU Eats loyalty points.',
                'loyalty_points_redeemed' => $pointsRequired,
            ]);
            OrderItem::create([
                'order_id' => $order->id,
                'food_id' => $food->id,
                'vendor_id' => $food->vendor_id,
                'food_name' => $food->name,
                'price' => $food->price,
                'quantity' => 1,
                'status' => 'received',
            ]);
            $customer->decrement('loyalty_points', $pointsRequired);
            $customer->loyaltyTransactions()->create([
                'order_id' => $order->id,
                'type' => 'redeemed',
                'points' => $pointsRequired,
                'description' => 'Purchased '.$food->name.' with points in order #'.$order->id,
                'reference' => 'order-'.$order->id.'-redeemed',
            ]);

            return $order;
        });

        $order->user->notify(new OrderUpdateNotification($order, 'Your meal was purchased with loyalty points and sent to the vendor.'));
        OrderStatusUpdated::dispatch($order->id);

        return redirect()->route('orders.show', $order)->with('success', 'Meal purchased with '.$order->loyalty_points_redeemed.' points. Your vendor is preparing it for pickup.');
    }
}
