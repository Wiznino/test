<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function index(): View
    {
        $vendors = User::query()->where('role', 'vendor')->withCount('foods')->orderBy('name')->paginate(20);
        $performance = $this->performanceFor($vendors->getCollection()->modelKeys());

        return view('admin.vendors.index', compact('vendors', 'performance'));
    }

    public function show(User $vendor): View
    {
        abort_unless($vendor->role === 'vendor', 404);
        $vendor->loadCount('foods');
        $performance = $this->performanceFor([$vendor->id])->get($vendor->id);
        $foods = $vendor->foods()->orderBy('name')->get();
        $orders = $vendor->vendorOrderItems()->with('order.user')->latest()->paginate(15);

        return view('admin.vendors.show', compact('vendor', 'performance', 'foods', 'orders'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->where('role', 'vendor')],
            'password' => 'required|string|min:8|confirmed',
        ]);
        $vendor = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'vendor',
            'is_active' => true,
        ]);

        return back()->with('success', 'Vendor account created. They can sign in with their email address and temporary password.');
    }

    public function updateStatus(Request $request, User $vendor): RedirectResponse
    {
        abort_unless($vendor->role === 'vendor', 404);
        $request->validate(['is_active' => 'required|boolean']);
        $vendor->update(['is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Vendor access updated.');
    }

    private function performanceFor(array $vendorIds): Collection
    {
        return OrderItem::query()
            ->select('vendor_id', DB::raw('COUNT(DISTINCT order_id) as orders_count'), DB::raw('SUM(quantity) as meals_sold'), DB::raw('SUM(price * quantity) as revenue'))
            ->whereIn('vendor_id', $vendorIds)
            ->whereHas('order', fn ($query) => $query->where('payment_status', 'paid')->where('status', '!=', 'cancelled')->where('payment_method', '!=', 'loyalty'))
            ->groupBy('vendor_id')
            ->get()
            ->keyBy('vendor_id');
    }
}
