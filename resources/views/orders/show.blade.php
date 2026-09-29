@extends('layouts.app')
@section('title', 'Order #'.str_pad($order->id, 4, '0', STR_PAD_LEFT))
@section('content')
<section class="page-shell order-detail-shell">
    <div class="page-kicker"><a href="{{ route('orders.index') }}">← All orders</a><span>ORDER #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</span></div>
    <div class="order-confirm"><span class="confirm-mark">✓</span><div class="eyebrow" id="order-status-label">{{ $order->payment_status === 'paid' ? strtoupper(str_replace('_', ' ', $order->status)) : 'PAYMENT PENDING' }}</div><h1>Your order is <em id="order-status-heading">{{ $order->payment_status !== 'paid' ? 'awaiting payment.' : ($order->status === 'ready' ? 'ready.' : ($order->status === 'completed' ? 'complete.' : ($order->status === 'preparing' ? 'being prepared.' : 'confirmed.'))) }}</em></h1><p id="order-status-message">{{ $order->payment_status !== 'paid' ? 'Complete your Paystack payment to confirm this order. The vendor can prepare it after payment is confirmed.' : ($order->status === 'ready' ? 'Your meal is ready. Head to the pickup point at your selected time.' : ($order->status === 'completed' ? 'Your order is complete. Thank you for ordering with ATU Eats.' : ($order->status === 'preparing' ? 'The vendor is preparing your meal.' : 'Payment confirmed. Your order has been sent to the vendor.'))) }}</p><div class="pickup-callout"><span>◷</span><div><small>YOUR PICKUP TIME</small><b>{{ $order->pickup_time->format('l, M j · g:i A') }}</b></div></div>@if($order->payment_status === 'pending')<div class="payment-pending"><strong>Payment not confirmed</strong><p>Your order will be sent to the vendor after Paystack confirms payment.</p><form method="POST" action="{{ route('payments.retry', $order) }}">@csrf<button class="button button-primary" type="submit">Continue to payment</button></form></div>@endif</div>
    <div class="summary-card order-summary-detail"><div class="eyebrow">YOUR ORDER · LIVE STATUS</div><h2>Order #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</h2><div class="summary-line"><span>Payment <small class="payment-method-label">via {{ $order->payment_method === 'wallet' ? 'ATU Eats wallet' : 'Paystack' }}</small></span><strong id="payment-status" class="payment-pill payment-{{ $order->payment_status }}">{{ $order->payment_status === 'paid' ? 'Paid' : 'Awaiting payment' }}</strong></div>@foreach($order->items as $item)<div class="summary-line tracking-item" data-item-id="{{ $item->id }}"><span>{{ $item->quantity }} × {{ $item->food_name }}<small class="tracking-status">{{ ucfirst($item->status ?? 'received') }}</small></span><strong><x-money :amount="$item->price * $item->quantity" /></strong></div>@endforeach<div class="summary-total"><span>Total</span><strong><x-money :amount="$order->total" /></strong></div>@if($order->notes)<p class="order-note">Kitchen note: {{ $order->notes }}</p>@endif</div><a class="text-link" href="{{ route('menu') }}">Explore the menu →</a>
</section>
@endsection
@push('scripts')
<style>.tracking-item{align-items:flex-start}.tracking-status{display:block;margin-top:5px;color:#0755a0;font-size:.72rem;font-weight:800}.payment-pending{margin-top:1.5rem;padding:1rem;background:#fff8df;border-radius:12px}.payment-pending p{margin:.4rem 0 1rem}</style>
@if(config('broadcasting.default') === 'reverb' && config('reverb.apps.apps.0.key') && config('reverb.apps.apps.0.options.host'))
<script src="https://js.pusher.com/8.4/pusher.min.js"></script>
@endif
<script>
(() => {
    const url = @json(route('orders.tracking', $order));
    const statusLabel = document.getElementById('order-status-label');
    const heading = document.getElementById('order-status-heading');
    const message = document.getElementById('order-status-message');
    const applyStatus = data => {
        if (data.payment_status === 'paid' && document.getElementById('payment-status')?.textContent.toLowerCase() !== 'paid') {
            window.location.reload();
            return;
        }
        if (data.payment_status !== 'paid') {
            statusLabel.textContent = 'PAYMENT PENDING';
            heading.textContent = 'awaiting payment.';
            message.textContent = 'Complete your Paystack payment to confirm this order. The vendor can prepare it after payment is confirmed.';
            return;
        }
        statusLabel.textContent = data.status.replaceAll('_', ' ').toUpperCase();
        heading.textContent = data.status === 'ready' ? 'ready.' : (data.status === 'completed' ? 'complete.' : (data.status === 'preparing' ? 'being prepared.' : 'confirmed.'));
        message.textContent = data.status === 'ready' ? 'Your meal is ready. Head to the pickup point at your selected time.' : (data.status === 'completed' ? 'Your order is complete. Thank you for ordering with ATU Eats.' : (data.status === 'preparing' ? 'The vendor is preparing your meal.' : 'Payment confirmed. Your order has been sent to the vendor.'));
        data.items.forEach(item => {
            const row = document.querySelector(`[data-item-id="${item.id}"] .tracking-status`);
            if (row) row.textContent = item.status.charAt(0).toUpperCase() + item.status.slice(1);
        });
    };
    const poll = async () => {
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (response.ok) applyStatus(await response.json());
        } catch {}
    };
    let socketConnected = false;
    @if(config('broadcasting.default') === 'reverb' && config('reverb.apps.apps.0.key') && config('reverb.apps.apps.0.options.host'))
    if (window.Pusher) {
        const pusher = new Pusher(@json(config('reverb.apps.apps.0.key')), {
            wsHost: @json(config('reverb.apps.apps.0.options.host')),
            wsPort: Number(@json(config('reverb.apps.apps.0.options.port'))),
            wssPort: Number(@json(config('reverb.apps.apps.0.options.port'))),
            forceTLS: @json(config('reverb.apps.apps.0.options.scheme') === 'https'),
            enabledTransports: ['ws', 'wss'],
            channelAuthorization: {
                endpoint: '/broadcasting/auth',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            },
        });
        pusher.connection.bind('state_change', state => { socketConnected = state.current === 'connected'; });
        pusher.subscribe('private-orders.{{ $order->id }}').bind('order.status.updated', data => {
            applyStatus(data);
        });
    }
    @endif
    window.setInterval(() => { if (!socketConnected) poll(); }, 5000);
    window.setTimeout(poll, 1000);
})();
</script>
@endpush
