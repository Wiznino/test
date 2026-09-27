<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function updateAvailability(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'accepting_orders' => ['required', 'boolean'],
            'opening_time' => ['nullable', 'date_format:H:i', 'required_with:closing_time'],
            'closing_time' => ['nullable', 'date_format:H:i', 'required_with:opening_time'],
        ]);

        Auth::user()->update([
            'accepting_orders' => $request->boolean('accepting_orders'),
            'opening_time' => $data['opening_time'] ?? null,
            'closing_time' => $data['closing_time'] ?? null,
        ]);

        return back()->with('success', 'Your order availability has been updated.');
    }

    public function __invoke(): View
    {
        $vendor = Auth::user();
        $paidItems = $vendor->vendorOrderItems()->whereHas('order', fn ($query) => $query->where('payment_status', 'paid'));
        $stats = [
            'meals' => $vendor->foods()->count(),
            'available' => $vendor->foods()->where('available', true)->count(),
            'orders' => (clone $paidItems)->distinct('order_id')->count('order_id'),
            'sales' => (clone $paidItems)->whereIn('status', ['ready', 'completed'])->selectRaw('SUM(price * quantity) as total')->value('total') ?? 0,
        ];
        $recentItems = $paidItems->with('order.user')->latest()->take(8)->get();

        return view('vendor.dashboard', compact('stats', 'recentItems'));
    }
}
