<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

Route::get('/', function () {
    return view('home');
});

Route::get('/register', function () {
    return view('register');
});

Route::get('/register', function () {
    return view('register');
});

Route::post('/register', function (Request $request) {

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'required|string|max:20',
        'password' => 'required|min:8|confirmed',
    ]);

    User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'phone' => $validated['phone'],
        'password' => Hash::make($validated['password']),
    ]);

    return redirect('/login')->with(
        'success',
        'Account created successfully! Please login.'
    );
});

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', function (Request $request) {

    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        return redirect('/dashboard');
    }

    return back()->withErrors([
        'email' => 'The email or password is incorrect.',
    ])->onlyInput('email');
});

Route::post('/logout', function (Request $request) {

    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');
Route::get('/menu', function () {
    $foods = \App\Models\Food::where('available', true)->get();
    return view('menu', compact('foods'));
})->middleware('auth');
Route::post('/cart/add/{id}', function ($id) {
    $food = \App\Models\Food::findOrFail($id);

    $cart = session()->get('cart', []);

    if (isset($cart[$id])) {
        $cart[$id]['quantity']++;
    } else {
        $cart[$id] = [
            'name' => $food->name,
            'price' => $food->price,
            'quantity' => 1,
        ];
    }

    session()->put('cart', $cart);

    return redirect('/menu')->with('success', $food->name . ' added to cart!');
})->middleware('auth');
Route::post('/cart/add/{id}', function ($id) {
    $food = \App\Models\Food::findOrFail($id);

    $cart = session()->get('cart', []);

    if (isset($cart[$id])) {
        $cart[$id]['quantity']++;
    } else {
        $cart[$id] = [
            'name' => $food->name,
            'price' => $food->price,
            'quantity' => 1,
        ];
    }

    session(['cart' => $cart]);

    return redirect('/cart');
})->middleware('auth');
Route::get('/cart', function () {
    $cart = session()->get('cart', []);
    return view('cart', compact('cart'));
})->middleware('auth');
Route::get('/cart/remove/{id}', function ($id) {
    $cart = session()->get('cart', []);
    if (isset($cart[$id])) {
        unset($cart[$id]);
    }
        session(['cart' => $cart]);
    
    return redirect('/cart');
})->middleware('auth');
