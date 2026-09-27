<?php

use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\MealController;
use App\Http\Controllers\Vendor\OrderItemController as VendorOrderItemController;
use App\Models\Food;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $foods = Food::where('available', true)->take(3)->get();

    return view('home', compact('foods'));
})->name('home');

Route::get('/menu', function (Request $request) {
    $foods = Food::where('available', true)
        ->when($request->filled('q'), fn ($query) => $query->where(fn ($q) => $q->where('name', 'like', '%'.$request->q.'%')->orWhere('description', 'like', '%'.$request->q.'%')))
        ->get();

    return view('menu', compact('foods'));
})->name('menu');

Route::middleware('guest')->group(function () {
    Route::get('/register', fn () => view('register'))->name('register');
    Route::post('/register', function (Request $request) {
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email', 'phone' => 'required|string|max:20', 'password' => 'required|min:8|confirmed']);
        User::create($data);

        return redirect()->route('login')->with('success', 'Your account is ready. Sign in to place your order.');
    });
    Route::get('/login', fn () => view('login'))->name('login');
    Route::post('/login', function (Request $request) {
        $credentials = $request->validate(['identifier' => 'required|string', 'password' => 'required|string']);
        $identifier = trim($credentials['identifier']);
        $loginCredentials = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? ['email' => strtolower($identifier), 'password' => $credentials['password']]
            : ['phone' => $identifier, 'role' => 'vendor', 'password' => $credentials['password']];
        if (Auth::attempt($loginCredentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (! Auth::user()->is_active) {
                Auth::logout();

                return back()->withErrors(['identifier' => 'This account is currently inactive.'])->onlyInput('identifier');
            }
            $destination = match (Auth::user()->role) {
                'admin' => route('admin.dashboard'),
                'vendor' => route('vendor.dashboard'),
                default => route('dashboard'),
            };

            return Auth::user()->role === 'customer'
                ? redirect()->intended($destination)
                : redirect()->to($destination);
        }

        return back()->withErrors(['identifier' => 'Those details don’t match an account.'])->onlyInput('identifier');
    });
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');

Route::get('/dashboard', function () {
    if (Auth::user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    if (Auth::user()->role === 'vendor') {
        return redirect()->route('vendor.dashboard');
    }
    $orders = Auth::user()->orders()->latest()->take(3)->get();

    return view('dashboard', compact('orders'));
})->middleware('auth')->name('dashboard');

Route::post('/cart/add/{food}', function (Food $food) {
    abort_unless($food->available, 404);
    $cart = session('cart', []);
    $id = (string) $food->id;
    $cart[$id] = ['name' => $food->name, 'price' => (float) $food->price, 'quantity' => ($cart[$id]['quantity'] ?? 0) + 1];
    session(['cart' => $cart]);

    return back()->with('success', $food->name.' added to your bag.');
})->name('cart.add');

Route::get('/cart', function () {
    $cart = session('cart', []);

    return view('cart', compact('cart'));
})->name('cart');

Route::patch('/cart/{id}', function (Request $request, string $id) {
    $request->validate(['quantity' => 'required|integer|min:1|max:20']);
    $cart = session('cart', []);
    if (isset($cart[$id])) {
        $cart[$id]['quantity'] = (int) $request->quantity;
    }
    session(['cart' => $cart]);

    return back()->with('success', 'Bag updated.');
})->name('cart.update');

Route::delete('/cart/{id}', function (string $id) {
    $cart = session('cart', []);
    unset($cart[$id]);
    session(['cart' => $cart]);

    return back()->with('success', 'Item removed from your bag.');
})->name('cart.remove');

Route::middleware('auth')->group(function () {
    Route::get('/checkout', function () {
        if (empty(session('cart', []))) {
            return redirect()->route('cart');
        }

        return view('checkout');
    })->name('checkout');

    Route::post('/checkout', function (Request $request) {
        $data = $request->validate(['pickup_time' => 'required|date|after:now', 'notes' => 'nullable|string|max:500']);
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('menu')->with('error', 'Add a meal before checking out.');
        }
        $foods = Food::whereIn('id', array_keys($cart))->where('available', true)->get()->keyBy('id');
        if ($foods->count() !== count($cart)) {
            return redirect()->route('cart')->with('error', 'A meal in your bag is no longer available. Please review your bag.');
        }
        $order = DB::transaction(function () use ($cart, $foods, $data) {
            $total = collect($cart)->sum(fn ($item, $id) => (float) $foods[$id]->price * $item['quantity']);
            $order = Order::create(['user_id' => Auth::id(), 'total' => $total, 'status' => 'received', 'pickup_time' => $data['pickup_time'], 'notes' => $data['notes'] ?? null]);
            foreach ($cart as $id => $item) {
                OrderItem::create(['order_id' => $order->id, 'food_id' => $id, 'vendor_id' => $foods[$id]->vendor_id, 'food_name' => $foods[$id]->name, 'price' => $foods[$id]->price, 'quantity' => $item['quantity'], 'status' => 'received']);
            }

            return $order;
        });
        session()->forget('cart');

        return redirect()->route('orders.show', $order)->with('success', 'Order placed! We’ll have it ready for your pickup time.');
    })->name('checkout.place');

    Route::get('/orders', function () {
        $orders = Auth::user()->orders()->with('items')->latest()->paginate(8);

        return view('orders.index', compact('orders'));
    })->name('orders.index');

    Route::get('/orders/{order}', function (Order $order) {
        abort_unless($order->user_id === Auth::id(), 403);
        $order->load('items');

        return view('orders.show', compact('order'));
    })->name('orders.show');

    Route::get('/orders/{order}/tracking', [OrderTrackingController::class, 'show'])->name('orders.tracking');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');
    Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
    Route::post('/vendors', [VendorController::class, 'store'])->name('vendors.store');
    Route::get('/vendors/{vendor}', [VendorController::class, 'show'])->name('vendors.show');
    Route::patch('/vendors/{vendor}/status', [VendorController::class, 'updateStatus'])->name('vendors.status');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
});

Route::prefix('vendor')->name('vendor.')->middleware(['auth', 'role:vendor'])->group(function () {
    Route::get('/', VendorDashboardController::class)->name('dashboard');
    Route::get('/meals', [MealController::class, 'index'])->name('meals.index');
    Route::get('/meals/create', [MealController::class, 'create'])->name('meals.create');
    Route::post('/meals', [MealController::class, 'store'])->name('meals.store');
    Route::get('/meals/{food}/edit', [MealController::class, 'edit'])->name('meals.edit');
    Route::put('/meals/{food}', [MealController::class, 'update'])->name('meals.update');
    Route::delete('/meals/{food}', [MealController::class, 'destroy'])->name('meals.destroy');
    Route::get('/orders', [VendorOrderItemController::class, 'index'])->name('orders.index');
    Route::patch('/orders/items/{orderItem}', [VendorOrderItemController::class, 'updateStatus'])->name('orders.status');
});
