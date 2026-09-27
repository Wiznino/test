<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $vendor = Auth::user();
        $stats = [
            'meals' => $vendor->foods()->count(),
            'available' => $vendor->foods()->where('available', true)->count(),
            'orders' => $vendor->vendorOrderItems()->distinct('order_id')->count('order_id'),
            'sales' => $vendor->vendorOrderItems()->whereIn('status', ['ready', 'completed'])->selectRaw('SUM(price * quantity) as total')->value('total') ?? 0,
        ];
        $recentItems = $vendor->vendorOrderItems()->with('order.user')->latest()->take(8)->get();

        return view('vendor.dashboard', compact('stats', 'recentItems'));
    }
}
