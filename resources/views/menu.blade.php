@extends('layouts.app')
@section('title', 'Explore the menu')
@section('content')
<x-promotion-strip :promotions="$promotions" />
@if($selectedPromotion)
<section class="promotion-feature" id="promotion-order" aria-labelledby="promotion-feature-title">
    <img class="promotion-feature-image" src="{{ $selectedPromotion->image_url }}" alt="{{ $selectedPromotion->food->name }} promotional meal">
    <div class="promotion-feature-copy">
        <span class="eyebrow"><span class="eyebrow-dot"></span> PROMOTIONAL MEAL</span>
        <h2 id="promotion-feature-title">{{ $selectedPromotion->title }}</h2>
        <p>{{ $selectedPromotion->description }}</p>
        @if($selectedPromotion->ends_at)
            <small class="promotion-feature-expiry">Offer ends {{ $selectedPromotion->ends_at->timezone(config('app.timezone'))->format('M j, g:i A') }}</small>
        @endif
        @php($promotionalFood = $selectedPromotion->food)
        @php($canOrderPromotionalFood = $promotionalFood->available && (!$promotionalFood->vendor || $promotionalFood->vendor->isAcceptingOrders()))
        <div class="promotion-order-details">
            <span class="promotion-order-label">The meal in this offer</span>
            <h3>{{ $promotionalFood->name }}</h3>
            <strong><x-money :amount="$promotionalFood->price" /></strong>
            @if($canOrderPromotionalFood)
                <form action="{{ route('cart.add', $promotionalFood) }}" method="POST">
                    @csrf
                    <button class="button button-primary" type="submit">Add to cart <span aria-hidden="true">+</span></button>
                </form>
            @else
                <p class="promotion-unavailable">This meal is currently unavailable.</p>
            @endif
        </div>
    </div>
</section>
@else
<section class="menu-intro"><div class="eyebrow"><span class="eyebrow-dot"></span> COOKED FRESH ON CAMPUS</div><h1>What sounds <em>good?</em></h1><p>Find something delicious for your next break.</p><form class="search-form" action="{{ route('menu') }}" method="GET"><span>⌕</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Search meals or ingredients" aria-label="Search menu"><button type="submit">Search</button></form><div class="menu-meta"><span>{{ $foods->count() }} delicious {{ Str::plural('option', $foods->count()) }}</span><span>Prepared fresh daily <b>✳</b></span></div></section>
<section class="menu-list" id="menu-items"><div class="food-grid menu-grid">
@forelse($foods as $food)
<article class="food-card"><div class="food-image"><img src="{{ $food->image_url ?: '/images/meal-placeholder.svg' }}" alt="{{ $food->name }}" loading="lazy" onerror="this.onerror=null;this.src='/images/meal-placeholder.svg'"><span class="food-label">FRESH TODAY</span><x-meal-save-button :food="$food" :is-saved="in_array($food->id, $favoriteFoodIds)" /></div><div class="food-info"><div><h3>{{ $food->name }}</h3>@if($food->reviews_count)@php($foodRatingAverage = collect([$food->portion_rating_average, $food->value_rating_average, $food->accuracy_rating_average])->filter(fn ($rating) => $rating !== null)->avg())<div class="food-rating" aria-label="ATU customer verified rating">&#9733; {{ number_format($foodRatingAverage, 1) }} <small>{{ $food->reviews_count }} verified</small></div>@endif<p>{{ $food->description ?: 'A campus classic, prepared fresh and ready for your day.' }}</p></div><div class="food-bottom"><strong><x-money :amount="$food->price" /></strong><form action="{{ route('cart.add', $food) }}" method="POST">@csrf<button class="add-button" aria-label="Add {{ $food->name }} to cart">+</button></form></div></div></article>
@empty
<div class="empty-featured"><span>⌕</span><h3>No meals found just yet.</h3><p>Try another search or browse the full menu.</p><a class="text-link" href="{{ route('menu') }}">Clear search ↗</a></div>
@endforelse
</div></section>
@endif
@unless($selectedPromotion)
<section class="menu-bottom-note"><span>✳</span> Can’t decide? Jollof is always a good idea. <a href="{{ route('cart') }}">View your cart ↗</a></section>
@endunless
@endsection
