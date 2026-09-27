<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('orders.{orderId}', function (User $user, int $orderId): bool {
    $order = Order::with('items:id,order_id,vendor_id')->find($orderId);

    if (! $order) {
        return false;
    }

    return $user->id === $order->user_id
        || $user->role === 'admin'
        || ($user->role === 'vendor' && $order->items->contains('vendor_id', $user->id));
});
