@if($promotions->isNotEmpty())
<section class="promotion-strip" aria-label="Campus promotions">
    <div class="promotion-strip-heading"><div><span class="eyebrow">JUST FOR THE ATU COMMUNITY</span><h2>Fresh <em>offers.</em></h2></div><span>{{ $promotions->count() }} {{ Str::plural('offer', $promotions->count()) }}</span></div>
    <div class="promotion-grid">
        @foreach($promotions as $promotion)
            <article class="promotion-card">
                @if($promotion->image_url)<img src="{{ $promotion->image_url }}" alt="{{ $promotion->title }}" loading="lazy">@endif
                <div class="promotion-copy"><span class="promotion-tag">ATU EATS OFFER</span><h3>{{ $promotion->title }}</h3><p>{{ $promotion->description }}</p>
                    @if($promotion->ends_at)<small class="promotion-expiry">Offer ends {{ $promotion->ends_at->timezone(config('app.timezone'))->format('M j, g:i A') }}</small>@endif
                    @if($promotion->link_url)<a href="{{ $promotion->link_url }}" target="_blank" rel="noopener noreferrer">View offer <span aria-hidden="true">↗</span></a>@endif
                </div>
            </article>
        @endforeach
    </div>
</section>
@endif
