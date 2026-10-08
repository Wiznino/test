@if($promotions->isNotEmpty())
    <section class="promotion-strip" aria-label="Campus promotions">
        <div class="promotion-strip-label">
            <span aria-hidden="true">&#9733;</span>
            <strong>ATU EATS</strong>
            <small>OFFERS</small>
        </div>

        <div class="promotion-ticker-window" aria-label="Current offers">
            <div class="promotion-ticker-track" style="--ticker-duration: {{ max(18, $promotions->count() * 8) }}s">
                @foreach([false, true] as $isDuplicate)
                    <div class="promotion-ticker-group" @if($isDuplicate) aria-hidden="true" inert @endif>
                        @foreach($promotions as $promotion)
                            <a class="promotion-ticker-item" href="{{ route('menu', ['promotion' => $promotion->id]) }}#promotion-order" aria-label="View {{ $promotion->title }} and order the promotional meal">
                                <span class="promotion-ticker-spark" aria-hidden="true">&#9733;</span>
                                <span class="promotion-copy">
                                    <span class="promotion-title">{{ $promotion->title }}</span>
                                    <span class="promotion-description">{{ $promotion->description }}</span>
                                    @if($promotion->ends_at)
                                        <small class="promotion-expiry">Ends {{ $promotion->ends_at->timezone(config('app.timezone'))->format('M j, g:i A') }}</small>
                                    @endif
                                </span>
                                <span class="promotion-shop-hint">Shop offer &rarr;</span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
