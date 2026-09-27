@extends('layouts.app')

@section('title', 'Your bag')

@section('content')
@php($total = collect($cart)->sum(fn ($item) => (float) $item['price'] * $item['quantity']))
<section class="page-shell">
    <div class="page-kicker"><a href="{{ route('menu') }}">Back to menu</a><span>YOUR ORDER</span></div>
    <div class="page-title-row">
        <div><div class="eyebrow">A GOOD CHOICE</div><h1>Your <em>bag.</em></h1></div>
        <span class="count-pill">{{ collect($cart)->sum('quantity') }} {{ Str::plural('item', collect($cart)->sum('quantity')) }}</span>
    </div>

    @if (count($cart))
        <div class="cart-layout">
            <div class="cart-items">
                @foreach ($cart as $id => $item)
                    <article class="cart-item">
                        <div class="cart-thumb"><img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=300&q=75" alt=""></div>
                        <div class="cart-item-copy">
                            <span class="eyebrow">FRESHLY PREPARED</span>
                            <h2>{{ $item['name'] }}</h2>
                            <span class="muted">GH&#8373; {{ number_format($item['price'], 2) }} each</span>
                            <div class="cart-controls">
                                <form action="{{ route('cart.update', $id) }}" method="POST" class="quantity-form">
                                    @csrf
                                    @method('PATCH')
                                    <label class="sr-only" for="qty-{{ $id }}">Quantity for {{ $item['name'] }}</label>
                                    <input id="qty-{{ $id }}" name="quantity" type="number" min="1" max="20" value="{{ $item['quantity'] }}">
                                    <button type="submit">Update</button>
                                </form>
                                <form action="{{ route('cart.remove', $id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="remove-button" type="submit">Remove</button>
                                </form>
                            </div>
                        </div>
                        <strong class="line-price">GH&#8373; {{ number_format((float) $item['price'] * $item['quantity'], 2) }}</strong>
                    </article>
                @endforeach
                <a class="text-link continue-link" href="{{ route('menu') }}">Add another meal</a>
            </div>

            <aside class="summary-card">
                <div class="eyebrow">YOUR TOTAL</div>
                <h2>Order summary</h2>
                <div class="summary-line"><span>Subtotal</span><strong>GH&#8373; {{ number_format($total, 2) }}</strong></div>
                <div class="summary-line"><span>Pickup</span><strong>Free</strong></div>
                <div class="summary-total"><span>Total</span><strong>GH&#8373; {{ number_format($total, 2) }}</strong></div>
                <a class="button button-primary button-wide" href="{{ route('checkout') }}">Continue to pickup</a>
                <p class="summary-note">Freshly prepared when you order.</p>
            </aside>
        </div>
    @else
        <div class="empty-cart">
            <span class="empty-bag">+</span>
            <h2>Your bag is taking a break.</h2>
            <p>There is plenty of good food waiting for you.</p>
            <a class="button button-primary" href="{{ route('menu') }}">Explore the menu</a>
        </div>
    @endif
</section>
@endsection
