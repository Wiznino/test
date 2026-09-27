<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderItemController extends Controller
{
    public function index(): View
    {
        $items = Auth::user()->vendorOrderItems()->with('order.user')->latest()->paginate(20);

        return view('vendor.orders.index', compact('items'));
    }

    public function updateStatus(Request $request, OrderItem $orderItem): RedirectResponse
    {
        abort_unless($orderItem->vendor_id === Auth::id(), 404);
        $data = $request->validate(['status' => 'required|in:received,preparing,ready,completed']);
        $orderItem->update($data);
        $statuses = $orderItem->order->items()->pluck('status');
        $orderStatus = $statuses->every(fn ($status) => $status === 'completed') ? 'completed' : ($statuses->every(fn ($status) => in_array($status, ['ready', 'completed'], true)) ? 'ready' : ($statuses->contains(fn ($status) => in_array($status, ['preparing', 'ready', 'completed'], true)) ? 'preparing' : 'received'));
        $orderItem->order->update(['status' => $orderStatus]);

        return back()->with('success', 'Order progress updated.');
    }
}
