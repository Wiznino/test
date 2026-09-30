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
                            <article class="promotion-ticker-item">
                                <span class="promotion-ticker-spark" aria-hidden="true">&#9733;</span>
                                <div class="promotion-copy">
                                    <h2>{{ $promotion->title }}</h2>
                                    <p>{{ $promotion->description }}</p>
                                    @if($promotion->ends_at)
                                        <small class="promotion-expiry">Ends {{ $promotion->ends_at->timezone(config('app.timezone'))->format('M j, g:i A') }}</small>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
