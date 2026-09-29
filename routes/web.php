<?php

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\PaystackPaymentController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\MealController;
use App\Http\Controllers\Vendor\OrderItemController as VendorOrderItemController;
use App\Http\Controllers\WalletController;
use App\Models\Food;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promotion;
use App\Models\User;
use App\Notifications\OrderUpdateNotification;
use App\Services\PaystackService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

Route::get('/', function () {
    $foods = Food::where('available', true)->with('vendor')->get()
        ->filter(fn (Food $food) => ! $food->vendor || $food->vendor->isAcceptingOrders())->take(3);
    $promotions = Promotion::currentlyVisible()->latest()->get();

    return view('home', compact('foods', 'promotions'));
})->name('home');

Route::get('/menu', function (Request $request) {
    $foods = Food::where('available', true)->with('vendor')
        ->when($request->filled('q'), fn ($query) => $query->where(fn ($q) => $q->where('name', 'like', '%'.$request->q.'%')->orWhere('description', 'like', '%'.$request->q.'%')))
        ->get()->filter(fn (Food $food) => ! $food->vendor || $food->vendor->isAcceptingOrders());
    $promotions = Promotion::currentlyVisible()->latest()->get();

    return view('menu', compact('foods', 'promotions'));
})->name('menu');

Route::middleware('guest')->group(function () {
    Route::get('/register', fn () => view('register'))->name('register');
    Route::post('/register', function (Request $request) {
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email', 'phone' => 'required|string|max:20', 'password' => 'required|min:8|confirmed']);
        User::create($data);

        return redirect()->route('login')->with('success', 'Your account is ready. Sign in to place orders and track updates.');
    })->middleware('throttle:5,1');
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
    })->middleware('throttle:login');

    Route::get('/forgot-password', fn () => view('auth.forgot-password'))->name('password.request');
    Route::post('/forgot-password', function (Request $request) {
        $request->validate(['email' => ['required', 'email']]);
        $status = Password::sendResetLink($request->only('email'));

        return back()->with($status === Password::RESET_LINK_SENT ? 'success' : 'error', __($status));
    })->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', fn (string $token, Request $request) => view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]))->name('password.reset');
    Route::post('/reset-password', function (Request $request) {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);
        $status = Password::reset($data, function (User $user, string $password): void {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    })->middleware('throttle:5,1')->name('password.update');
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
    $promotions = Promotion::currentlyVisible()->latest()->get();

    return view('dashboard', compact('orders', 'promotions'));
})->middleware('auth')->name('dashboard');

Route::post('/cart/add/{food}', function (Food $food) {
    abort_unless($food->available, 404);
    $cart = session('cart', []);
    $id = (string) $food->id;
    $cart[$id] = ['name' => $food->name, 'price' => (float) $food->price, 'image' => $food->image_url, 'quantity' => ($cart[$id]['quantity'] ?? 0) + 1];
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
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart');
        }
        $minimumPickup = now()->addMinutes(Food::whereIn('id', array_keys($cart))->max('preparation_minutes') ?? 15);

        return view('checkout', compact('minimumPickup'));
    })->name('checkout');

    Route::post('/checkout', function (Request $request, PaystackService $paystack) {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('menu')->with('error', 'Add a meal before checking out.');
        }
        $foods = Food::whereIn('id', array_keys($cart))->where('available', true)->with('vendor')->get()->keyBy('id');
        if ($foods->count() !== count($cart)) {
            return redirect()->route('cart')->with('error', 'A meal in your bag is no longer available. Please review your bag.');
        }
        foreach ($foods as $food) {
            if ($food->vendor && ! $food->vendor->isAcceptingOrders()) {
                return redirect()->route('cart')->with('error', $food->vendor->name.' is not accepting orders right now.');
            }
        }
        $minimumPickup = now()->addMinutes($foods->max('preparation_minutes') ?? 15);
        $data = $request->validate([
            'pickup_time' => ['required', 'date', 'after:'.$minimumPickup->format('Y-m-d H:i:s')],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:paystack,wallet'],
        ]);
        if ($data['payment_method'] === 'paystack' && ! config('services.paystack.secret_key')) {
            return back()->withInput()->with('error', 'Online payments are not configured yet. Please contact the cafeteria.');
        }
        [$order, $paidByWallet] = DB::transaction(function () use ($cart, $foods, $data) {
            $customer = User::query()->lockForUpdate()->findOrFail(Auth::id());
            $totalPesewas = collect($cart)->sum(fn ($item, $id) => (int) round((float) $foods[$id]->price * 100) * $item['quantity']);
            $total = $totalPesewas / 100;
            $paidByWallet = $data['payment_method'] === 'wallet';
            if ($paidByWallet && (int) round((float) $customer->wallet_balance * 100) < $totalPesewas) {
                throw ValidationException::withMessages(['payment_method' => 'Your wallet balance is too low. Top it up or select Paystack.']);
            }
            $order = Order::create([
                'user_id' => $customer->id,
                'total' => $total,
                'status' => $paidByWallet ? 'received' : 'awaiting_payment',
                'payment_status' => $paidByWallet ? 'paid' : 'pending',
                'payment_method' => $data['payment_method'],
                'paid_at' => $paidByWallet ? now() : null,
                'pickup_time' => $data['pickup_time'],
                'notes' => $data['notes'] ?? null,
            ]);
            foreach ($cart as $id => $item) {
                OrderItem::create(['order_id' => $order->id, 'food_id' => $id, 'vendor_id' => $foods[$id]->vendor_id, 'food_name' => $foods[$id]->name, 'price' => $foods[$id]->price, 'quantity' => $item['quantity'], 'status' => 'received']);
            }

            if ($paidByWallet) {
                $customer->decrement('wallet_balance', $total);
                $customer->walletTransactions()->create([
                    'type' => 'debit',
                    'amount' => $total,
                    'status' => 'completed',
                    'reference' => 'atu-order-'.$order->id.'-'.Str::uuid(),
                    'description' => 'Payment for order #'.$order->id,
                    'completed_at' => now(),
                ]);
            }

            return [$order, $paidByWallet];
        });
        if ($paidByWallet) {
            session()->forget('cart');
            $order->user->notify(new OrderUpdateNotification($order, 'Your wallet payment was received. The cafeteria has your order.'));
            OrderStatusUpdated::dispatch($order->id);

            return redirect()->route('orders.show', $order)->with('success', 'Your order was paid with your ATU Eats wallet.');
        }
        try {
            $payment = $paystack->initialize($order);
        } catch (RuntimeException $exception) {
            return redirect()->route('orders.show', $order)->with('error', $exception->getMessage());
        }
        session()->forget('cart');

        return redirect()->away($payment['authorization_url']);
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
    Route::get('/payments/paystack/callback', [PaystackPaymentController::class, 'callback'])->name('payments.paystack.callback');
    Route::post('/orders/{order}/payment/retry', [PaystackPaymentController::class, 'retry'])->name('payments.retry');
});

Route::prefix('wallet')->name('wallet.')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/', [WalletController::class, 'show'])->name('show');
    Route::post('/top-ups', [WalletController::class, 'storeTopUp'])->name('topups.store');
    Route::post('/transactions/{walletTransaction}/retry', [WalletController::class, 'retryTopUp'])->name('topups.retry');
});

Route::post('/payments/paystack/webhook', [PaystackPaymentController::class, 'webhook'])
    ->withoutMiddleware(ValidateCsrfToken::class)
    ->name('payments.paystack.webhook');

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
    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
    Route::post('/promotions', [PromotionController::class, 'store'])->name('promotions.store');
    Route::put('/promotions/{promotion}', [PromotionController::class, 'update'])->name('promotions.update');
    Route::delete('/promotions/{promotion}', [PromotionController::class, 'destroy'])->name('promotions.destroy');
});

Route::prefix('vendor')->name('vendor.')->middleware(['auth', 'role:vendor'])->group(function () {
    Route::get('/', VendorDashboardController::class)->name('dashboard');
    Route::patch('/availability', [VendorDashboardController::class, 'updateAvailability'])->name('availability');
    Route::get('/meals', [MealController::class, 'index'])->name('meals.index');
    Route::get('/meals/create', [MealController::class, 'create'])->name('meals.create');
    Route::post('/meals', [MealController::class, 'store'])->name('meals.store');
    Route::get('/meals/{food}/edit', [MealController::class, 'edit'])->name('meals.edit');
    Route::patch('/meals/{food}/availability', [MealController::class, 'updateAvailability'])->name('meals.availability');
    Route::put('/meals/{food}', [MealController::class, 'update'])->name('meals.update');
    Route::delete('/meals/{food}', [MealController::class, 'destroy'])->name('meals.destroy');
    Route::get('/orders', [VendorOrderItemController::class, 'index'])->name('orders.index');
    Route::patch('/orders/items/{orderItem}', [VendorOrderItemController::class, 'updateStatus'])->name('orders.status');
});
