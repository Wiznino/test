@extends('layouts.app')
@section('title', 'Order #'.str_pad($order->id, 5, '0', STR_PAD_LEFT))
@section('content')
<section class="manage-shell">
    <a class="manage-text-link admin-back-link" href="{{ route('admin.orders.index') }}">&larr; All orders</a>
    <div class="manage-hero admin-detail-hero">
        <div>
            <span class="manage-kicker">ORDER #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
            <h1>{{ $order->user->name }}</h1>
            <p>Placed {{ $order->created_at->format('F j, Y, g:i A') }} | Pickup {{ $order->pickup_time->format('F j, g:i A') }}</p>
        </div>
        <span class="manage-status status-{{ $order->status }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
    </div>
    <div class="manage-stats">
        <article><small>Order total</small><strong><x-money :amount="$order->total" /></strong></article>
        <article><small>Customer</small><strong>{{ $order->user->name }}</strong></article>
        <article><small>Order status</small><strong>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</strong></article>
        <article><small>Payment</small><strong>{{ ucfirst($order->payment_status) }}</strong></article>
        <article><small>Number of meals</small><strong>{{ $order->items->sum('quantity') }}</strong></article>
    </div>

    <section class="manage-panel admin-section-spaced">
        <div class="manage-title">
            <div><span class="manage-kicker">CANCELLATION &amp; REFUND</span><h2>Order resolution</h2></div>
            <span class="manage-status status-{{ $order->refund_status }}">{{ ucfirst(str_replace('_', ' ', $order->refund_status)) }}</span>
        </div>
        <div class="admin-contact-grid">
            <div><small>Refund amount</small><span>@if($order->refund_amount)<x-money :amount="$order->refund_amount" />@else No refund due @endif</span></div>
            <div><small>Cancelled</small><span>{{ $order->cancelled_at?->format('M j, Y, g:i A') ?? 'Not cancelled' }}</span></div>
            <div><small>Refunded</small><span>{{ $order->refunded_at?->format('M j, Y, g:i A') ?? 'Not processed' }}</span></div>
            <div><small>Refund reference</small><span>{{ $order->refund_reference ?? 'Not recorded' }}</span></div>
            <div><small>Cancelled by</small><span>{{ $order->cancelledBy?->name ?? 'Not cancelled' }}</span></div>
            <div><small>Refund recorded by</small><span>{{ $order->refundedBy?->name ?? 'Not recorded' }}</span></div>
        </div>
        @if($order->cancellation_reason)<p class="admin-order-note"><strong>Cancellation reason:</strong> {{ $order->cancellation_reason }}</p>@endif
        @if($order->status !== 'cancelled' && $order->status !== 'completed')
            <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" class="admin-resolution-form">
                @csrf
                <label for="cancellation_reason">Cancellation reason (optional)</label>
                <textarea id="cancellation_reason" name="cancellation_reason" maxlength="500" rows="2">{{ old('cancellation_reason') }}</textarea>
                @error('cancellation_reason')<small class="form-error">{{ $message }}</small>@enderror
                <button class="manage-button" type="submit" onclick="return confirm('Cancel this order?')">Cancel order</button>
            </form>
        @endif
        @if($order->refund_status === 'pending' && $order->payment_method === 'paystack')
            <div class="payment-pending">
                <strong>Paystack refund needs processing</strong>
                <p>Process the refund in Paystack, then enter its reference here to record it as completed.</p>
                <form method="POST" action="{{ route('admin.orders.refund', $order) }}" class="admin-resolution-form">
                    @csrf
                    <label for="refund_reference">Paystack refund reference</label>
                    <input id="refund_reference" name="refund_reference" value="{{ old('refund_reference') }}" maxlength="100" required>
                    @error('refund_reference')<small class="form-error">{{ $message }}</small>@enderror
                    <button class="manage-button" type="submit">Record completed refund</button>
                </form>
            </div>
        @endif
        @error('order')<p class="form-error">{{ $message }}</p>@enderror
    </section>

    <section class="manage-panel admin-section-spaced">
        <div class="manage-title"><div><span class="manage-kicker">CUSTOMER DETAILS</span><h2>Contact and pickup</h2></div><a class="manage-text-link" href="{{ route('admin.customers.show', $order->user) }}">View customer profile</a></div>
        <div class="admin-contact-grid">
            <div><small>Email address</small><a href="mailto:{{ $order->user->email }}">{{ $order->user->email }}</a></div>
            <div><small>Phone number</small>@if($order->user->phone)<a href="tel:{{ $order->user->phone }}">{{ $order->user->phone }}</a>@else<span>Not provided</span>@endif</div>
            <div><small>Pickup time</small><span>{{ $order->pickup_time->format('l, F j, g:i A') }}</span></div>
            <div><small>Order placed</small><span>{{ $order->created_at->format('l, F j, g:i A') }}</span></div>
        </div>
        @if($order->notes)<p class="admin-order-note"><strong>Customer note:</strong> {{ $order->notes }}</p>@endif
    </section>

    <section class="manage-panel admin-section-spaced">
        <div class="manage-title"><div><span class="manage-kicker">ORDER CONTENTS</span><h2>Meals and vendors</h2></div></div>
        @forelse($order->items as $item)
            <div class="admin-order-row"><span class="vendor-avatar">{{ $item->quantity }}x</span><span>{{ $item->food_name }}<small>Vendor: {{ $item->vendor?->name ?? 'Unassigned' }}{{ $item->vendor?->phone ? ' | '.$item->vendor->phone : '' }}</small></span><span class="manage-status status-{{ $item->status }}">{{ ucfirst($item->status) }}</span><strong><x-money :amount="$item->price * $item->quantity" /></strong></div>
        @empty
            <p class="manage-empty">No meal details were recorded for this order.</p>
        @endforelse
        <div class="summary-total"><span>Order total</span><strong><x-money :amount="$order->total" /></strong></div>
    </section>
</section>
@endsection
