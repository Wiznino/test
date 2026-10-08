<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $orderId) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('orders.'.$this->orderId)];
    }

    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }

    public function broadcastWith(): array
    {
        $order = Order::with('items:id,order_id,food_name,quantity,status')->find($this->orderId);

        return [
            'status' => $order?->status,
            'payment_status' => $order?->payment_status,
            'refund_status' => $order?->refund_status,
            'refund_amount' => $order?->refund_amount,
            'updated_at' => $order?->updated_at?->toIso8601String(),
            'items' => $order?->items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->food_name,
                'quantity' => $item->quantity,
                'status' => $item->status,
            ])->values()->all() ?? [],
        ];
    }
}
