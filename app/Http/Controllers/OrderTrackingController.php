<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function show(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        $order->load('items:id,order_id,food_name,quantity,status');

        return response()->json([
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'refund_status' => $order->refund_status,
            'refund_amount' => $order->refund_amount,
            'updated_at' => $order->updated_at?->toIso8601String(),
            'items' => $order->items->map(fn ($item) => ['id' => $item->id, 'name' => $item->food_name, 'status' => $item->status])->values(),
        ]);
    }
}
