@extends('layouts.app')
@section('title', 'Campus rewards')
@section('content')
<section class="manage-shell loyalty-shell">
    <div class="manage-hero loyalty-hero">
        <div><span class="manage-kicker">ATU EATS REWARDS</span><h1>Your points bring a meal closer.</h1><p>Earn one point for every meal in a paid order. Five points equal GH₵1, and you can use your points to pay for an available meal.</p></div>
        <div class="loyalty-balance"><small>POINT BALANCE</small><strong>{{ number_format((float) Auth::user()->loyalty_points, 1) }}</strong><span>points</span></div>
    </div>
    @if($errors->any())<div class="inline-error">{{ $errors->first() }}</div>@endif
    <div class="loyalty-streak-card"><div><span class="manage-kicker">CAMPUS MEAL STREAK</span><h2>{{ $streak }} {{ Str::plural('day', $streak) }} in a row</h2><p>Place a paid order on consecutive days to keep your streak going.</p></div><div class="streak-milestones"><span class="{{ $streak >= 3 ? 'milestone-earned' : '' }}">3 days <b>+2 points</b></span><span class="{{ $streak >= 7 ? 'milestone-earned' : '' }}">7 days <b>+5 points</b></span></div></div>

    <section class="manage-panel loyalty-section">
        <div class="manage-title"><div><span class="manage-kicker">YOUR CAMPUS, YOUR PICK</span><h2>Vote for meal of the week</h2><p>Choose one available meal this week. Your vote is final until next week.</p></div><span class="manage-status">{{ now()->startOfWeek()->format('M j') }} – {{ now()->endOfWeek()->format('M j') }}</span></div>
        @if($foods->isEmpty())<p class="manage-empty">No meals are available to vote for right now.</p>@else
            <div class="loyalty-food-grid">
                @foreach($foods as $food)
                    <article class="loyalty-food-card">
                        <img src="{{ $food->image_url ?: '/images/meal-placeholder.svg' }}" alt="{{ $food->name }}" onerror="this.onerror=null;this.src='/images/meal-placeholder.svg'">
                        <div><h3>{{ $food->name }}</h3><small>{{ $food->vendor?->name ?? 'ATU Eats' }} · {{ $food->votes_count }} {{ Str::plural('vote', $food->votes_count) }}</small></div>
                        <form method="POST" action="{{ route('loyalty.votes.store') }}">@csrf<input type="hidden" name="food_id" value="{{ $food->id }}"><button class="manage-button" type="submit" @disabled($currentVote)>{{ $currentVote ? ((int) $currentVote->food_id === (int) $food->id ? 'Your vote is in' : 'Already voted this week') : 'Vote for this meal' }}</button></form>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="manage-panel loyalty-section">
        <div class="manage-title"><div><span class="manage-kicker">PAY WITH POINTS</span><h2>Choose a meal to purchase</h2><p>5 points cover GH₵1 of the meal price. Select a pickup slot to place your points-paid order.</p></div></div>
        @if($foods->isEmpty())<p class="manage-empty">Available meals will appear here when vendors add them.</p>@else
            <div class="loyalty-food-grid">
                @foreach($foods as $food)
                    @php($pointsRequired = \App\Services\LoyaltyPointsService::pointsRequiredForMeal((float) $food->price))
                    <article class="loyalty-food-card reward-food-card">
                        <img src="{{ $food->image_url ?: '/images/meal-placeholder.svg' }}" alt="{{ $food->name }}" onerror="this.onerror=null;this.src='/images/meal-placeholder.svg'">
                        <div><h3>{{ $food->name }}</h3><small><x-money :amount="$food->price" /> · {{ $food->vendor?->name ?? 'ATU Eats' }}</small><small class="loyalty-meal-cost">Costs {{ number_format($pointsRequired) }} points (5 points = GH₵1)</small></div>
                        <form method="POST" action="{{ route('loyalty.redeem') }}">@csrf<input type="hidden" name="food_id" value="{{ $food->id }}"><label>Pickup time<select name="pickup_time" required @disabled(Auth::user()->loyalty_points < $pointsRequired || $pickupSlotsByFood[$food->id]->isEmpty())><option value="">Choose a pickup slot</option>@foreach($pickupSlotsByFood[$food->id] as $slot)<option value="{{ $slot->format('Y-m-d\\TH:i') }}">{{ $slot->format('D, M j · g:i A') }}</option>@endforeach</select></label><button class="manage-button" type="submit" @disabled(Auth::user()->loyalty_points < $pointsRequired || $pickupSlotsByFood[$food->id]->isEmpty())>{{ $pickupSlotsByFood[$food->id]->isEmpty() ? 'No pickup slots' : (Auth::user()->loyalty_points < $pointsRequired ? 'Need '.$pointsRequired.' points' : 'Purchase with '.$pointsRequired.' points') }}</button></form>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="manage-panel loyalty-section">
        <div class="manage-title"><div><span class="manage-kicker">POINT ACTIVITY</span><h2>Your rewards history</h2></div></div>
        @forelse($transactions as $transaction)
            <div class="loyalty-transaction"><div><strong>{{ $transaction->description }}</strong><small>{{ $transaction->created_at->format('M j, Y · g:i A') }}</small></div><b class="{{ in_array($transaction->type, ['earned', 'streak_bonus', 'returned', 'daily_login'], true) ? 'points-positive' : 'points-negative' }}">{{ in_array($transaction->type, ['earned', 'streak_bonus', 'returned', 'daily_login'], true) ? '+' : '−' }}{{ number_format((float) $transaction->points, 1) }} pts</b></div>
        @empty<p class="manage-empty">Your earned points, streak bonuses, redemptions, and reversals will appear here.</p>@endforelse
        {{ $transactions->links() }}
    </section>
</section>
@endsection
