@extends('layouts.app')
@section('title', 'Order #'.str_pad($order->id, 4, '0', STR_PAD_LEFT))
@section('content')
<section class="page-shell order-detail-shell">
    <div class="page-kicker"><a href="{{ route('orders.index') }}">← All orders</a><span>ORDER #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</span></div>
    <div class="order-confirm"><span class="confirm-mark">✓</span><div class="eyebrow" id="order-status-label">{{ strtoupper($order->status) }}</div><h1>Your meal is <em id="order-status-heading">{{ $order->status === 'ready' ? 'ready.' : ($order->status === 'completed' ? 'complete.' : 'on its way.') }}</em></h1><p id="order-status-message">{{ $order->status === 'ready' ? 'Head to the pickup point and enjoy.' : 'We are preparing your order. This page updates automatically.' }}</p><div class="pickup-callout"><span>◷</span><div><small>YOUR PICKUP TIME</small><b>{{ $order->pickup_time->format('l, M j · g:i A') }}</b></div></div></div>
    <div class="summary-card order-summary-detail"><div class="eyebrow">YOUR ORDER · LIVE STATUS</div><h2>Freshly picked.</h2>@foreach($order->items as $item)<div class="summary-line tracking-item" data-item-id="{{ $item->id }}"><span>{{ $item->quantity }} × {{ $item->food_name }}<small class="tracking-status">{{ ucfirst($item->status ?? 'received') }}</small></span><strong>GH&#8373; {{ number_format($item->price * $item->quantity, 2) }}</strong></div>@endforeach<div class="summary-total"><span>Total</span><strong>GH&#8373; {{ number_format($order->total, 2) }}</strong></div>@if($order->notes)<p class="order-note">Kitchen note: {{ $order->notes }}</p>@endif</div><a class="text-link" href="{{ route('menu') }}">Explore the menu →</a>
</section>
@endsection
@push('scripts')
<style>.tracking-item{align-items:flex-start}.tracking-status{display:block;margin-top:5px;color:#0755a0;font-size:.72rem;font-weight:800}</style>
<script>
(() => {
    const url = @json(route('orders.tracking', $order));
    const statusLabel = document.getElementById('order-status-label');
    const heading = document.getElementById('order-status-heading');
    const message = document.getElementById('order-status-message');
    const poll = async () => {
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (!response.ok) return;
            const data = await response.json();
            statusLabel.textContent = data.status.replaceAll('_', ' ').toUpperCase();
            heading.textContent = data.status === 'ready' ? 'ready.' : (data.status === 'completed' ? 'complete.' : (data.status === 'preparing' ? 'being prepared.' : 'on its way.'));
            message.textContent = data.status === 'ready' ? 'Head to the pickup point and enjoy.' : (data.status === 'completed' ? 'Your order has been completed. Thank you!' : 'We are preparing your order. This page updates automatically.');
            data.items.forEach(item => {
                const row = document.querySelector(`[data-item-id="${item.id}"] .tracking-status`);
                if (row) row.textContent = item.status.charAt(0).toUpperCase() + item.status.slice(1);
            });
            if (data.status !== 'completed') window.setTimeout(poll, 5000);
        } catch { window.setTimeout(poll, 8000); }
    };
    window.setTimeout(poll, 5000);
})();
</script>
@endpush
