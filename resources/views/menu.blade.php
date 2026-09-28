@extends('layouts.app')
@section('title', 'Explore the menu')
@section('content')
<section class="menu-intro"><div class="eyebrow"><span class="eyebrow-dot"></span> COOKED FRESH ON CAMPUS</div><h1>What sounds <em>good?</em></h1><p>Find something delicious for your next break.</p><form class="search-form" action="{{ route('menu') }}" method="GET"><span>⌕</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Search meals or ingredients" aria-label="Search menu"><button type="submit">Search</button></form><div class="menu-meta"><span>{{ $foods->count() }} delicious {{ Str::plural('option', $foods->count()) }}</span><span>Prepared fresh daily <b>✳</b></span></div></section>
<section class="menu-list"><div class="food-grid menu-grid">
@forelse($foods as $food)
<article class="food-card"><div class="food-image"><img src="{{ $food->image_url ?: '/images/meal-placeholder.svg' }}" alt="{{ $food->name }}" loading="lazy" onerror="this.onerror=null;this.src='/images/meal-placeholder.svg'"><span class="food-label">FRESH TODAY</span><span class="heart-mark">✳</span></div><div class="food-info"><div><h3>{{ $food->name }}</h3><p>{{ $food->description ?: 'A campus classic, prepared fresh and ready for your day.' }}</p></div><div class="food-bottom"><strong>GH&#8373; {{ number_format($food->price, 2) }}</strong><form action="{{ route('cart.add', $food) }}" method="POST">@csrf<button class="add-button" aria-label="Add {{ $food->name }} to bag">+</button></form></div></div></article>
@empty
<div class="empty-featured"><span>⌕</span><h3>No meals found just yet.</h3><p>Try another search or browse the full menu.</p><a class="text-link" href="{{ route('menu') }}">Clear search ↗</a></div>
@endforelse
</div></section>
<section class="menu-bottom-note"><span>✳</span> Can’t decide? Jollof is always a good idea. <a href="{{ route('cart') }}">View your bag ↗</a></section>
@endsection
