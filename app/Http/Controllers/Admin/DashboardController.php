<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'users' => User::where('role', 'customer')->count(),
            'vendors' => User::where('role', 'vendor')->count(),
            'active_vendors' => User::where('role', 'vendor')->where('is_active', true)->count(),
            'orders' => Order::count(),
            'sales' => Order::whereIn('status', ['ready', 'completed'])->where('payment_status', 'paid')->where('payment_method', '!=', 'loyalty')->sum('total'),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'pending_refunds' => Order::where('refund_status', 'pending')->count(),
            'refunded_total' => Order::where('refund_status', 'refunded')->sum('refund_amount'),
        ];
        $vendors = User::where('role', 'vendor')->withCount('foods')->orderBy('name')->get();
        $performance = OrderItem::query()->select('vendor_id', DB::raw('COUNT(DISTINCT order_id) as orders_count'), DB::raw('SUM(quantity) as meals_sold'), DB::raw('SUM(price * quantity) as revenue'))
            ->whereNotNull('vendor_id')
            ->whereHas('order', fn ($query) => $query->where('payment_status', 'paid')->where('status', '!=', 'cancelled')->where('payment_method', '!=', 'loyalty'))
            ->groupBy('vendor_id')
            ->get()
            ->keyBy('vendor_id');

        return view('admin.dashboard', compact('stats', 'vendors', 'performance'));
    }
}
