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
            'sales' => Order::whereIn('status', ['ready', 'completed'])->sum('total'),
        ];
        $vendors = User::where('role', 'vendor')->withCount('foods')->orderBy('name')->get();
        $performance = OrderItem::query()->select('vendor_id', DB::raw('COUNT(DISTINCT order_id) as orders_count'), DB::raw('SUM(quantity) as meals_sold'), DB::raw('SUM(price * quantity) as revenue'))->whereNotNull('vendor_id')->groupBy('vendor_id')->get()->keyBy('vendor_id');

        return view('admin.dashboard', compact('stats', 'vendors', 'performance'));
    }
}
