<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FoodReviewController extends Controller
{
    public function store(Request $request, OrderItem $orderItem): RedirectResponse
    {
        $order = $orderItem->order;
        abort_unless(
            $order->user_id === Auth::id()
                && $order->payment_status === 'paid'
                && $orderItem->status === 'completed'
                && $orderItem->food_id !== null,
            403,
        );

        if ($orderItem->review()->exists()) {
            return back()->with('success', 'This completed meal has already been rated.');
        }

        $data = $request->validate([
            'portion_rating' => ['required', 'integer', 'between:1,5'],
            'value_rating' => ['required', 'integer', 'between:1,5'],
            'accuracy_rating' => ['required', 'integer', 'between:1,5'],
        ]);

        $orderItem->review()->create([
            ...$data,
            'user_id' => Auth::id(),
            'food_id' => $orderItem->food_id,
            'vendor_id' => $orderItem->vendor_id,
        ]);

        return back()->with('success', 'Thanks for sharing your verified meal rating.');
    }
}
