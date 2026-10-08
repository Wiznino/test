@extends('layouts.app')
@section('title', 'Saved meals')
@section('content')
<section class="page-shell favorites-page">
    <div class="eyebrow"><span class="eyebrow-dot"></span> YOUR PERSONAL MEAL LIST</div>
    <div class="page-title-row">
        <div><h1>Saved meals</h1><p class="favorites-intro">Keep the meals you love close for your next campus break.</p></div>
        <span class="count-pill">{{ $foods->count() }} {{ Str::plural('meal', $foods->count()) }}</span>
    </div>

    @if($foods->isNotEmpty())
        <div class="food-grid favorites-grid">
            @foreach($foods as $food)
                @php($isAvailable = $food->available && (!$food->vendor || $food->vendor->isAcceptingOrders()))
                <article class="food-card">
                    <div class="food-image">
                        <img src="{{ $food->image_url ?: '/images/meal-placeholder.svg' }}" alt="{{ $food->name }}" loading="lazy" onerror="this.onerror=null;this.src='/images/meal-placeholder.svg'">
                        <span class="food-label">{{ $isAvailable ? 'SAVED FOR LATER' : 'CURRENTLY UNAVAILABLE' }}</span>
                        <x-meal-save-button :food="$food" :is-saved="true" />
                    </div>
                    <div class="food-info">
                        <div>
                            <h3>{{ $food->name }}</h3>
                            @if($food->reviews_count)
                                @php($foodRatingAverage = collect([$food->portion_rating_average, $food->value_rating_average, $food->accuracy_rating_average])->filter(fn ($rating) => $rating !== null)->avg())
                                <div class="food-rating" aria-label="ATU customer verified rating">&#9733; {{ number_format($foodRatingAverage, 1) }} <small>{{ $food->reviews_count }} verified</small></div>
                            @endif
                            <p>{{ $food->description ?: 'A campus favourite, prepared fresh for you.' }}</p>
                        </div>
                        <div class="food-bottom">
                            <strong><x-money :amount="$food->price" /></strong>
                            @if($isAvailable)
                                <form action="{{ route('cart.add', $food) }}" method="POST">@csrf<button class="add-button" aria-label="Add {{ $food->name }} to cart">+</button></form>
                            @else
                                <span class="saved-unavailable">Unavailable</span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="favorites-empty">
            <span aria-hidden="true">&#9825;</span>
            <div><h2>Your saved list is ready for something tasty.</h2><p>Tap the heart on a meal to keep it here for next time.</p></div>
            <a class="button button-primary" href="{{ route('menu') }}">Explore meals <span aria-hidden="true">&rarr;</span></a>
        </div>
    @endif
</section>
@endsection
