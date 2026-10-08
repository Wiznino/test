@extends('layouts.app')
@section('title', 'Orders')
@section('content')
<section class="manage-shell">
    <div class="manage-title page-manage-title">
        <div><span class="manage-kicker">ADMINISTRATION | ORDER MONITOR</span><h1>All <em>orders</em></h1><p>Review customer orders, payment status, cancellations, and refunds.</p></div>
        <a class="manage-outline admin-blue-outline" href="{{ route('admin.dashboard') }}">&larr; Overview</a>
    </div>
    <form class="admin-search" method="GET" action="{{ route('admin.orders.index') }}">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search order number or customer">
        <select name="status" aria-label="Filter by order status">
            <option value="">All order statuses</option>
            @foreach(['awaiting_payment', 'received', 'preparing', 'ready', 'completed', 'cancelled'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <select name="refund_status" aria-label="Filter by refund status">
            <option value="">All refund statuses</option>
            @foreach(['none', 'pending', 'refunded'] as $refundStatus)
                <option value="{{ $refundStatus }}" @selected(request('refund_status') === $refundStatus)>{{ ucfirst($refundStatus) }}</option>
            @endforeach
        </select>
        <button class="manage-button" type="submit">Filter orders</button>
        @if(request()->hasAny(['q', 'status', 'refund_status']))<a class="manage-text-link" href="{{ route('admin.orders.index') }}">Clear</a>@endif
    </form>
    <div class="manage-panel admin-record-list">
        <div class="admin-order-heading"><span>Order</span><span>Customer</span><span>Placed / pickup</span><span>Status / refund</span><span>Total</span></div>
        @forelse($orders as $order)
            <a class="admin-order-list-row" href="{{ route('admin.orders.show', $order) }}">
                <strong class="admin-order-number">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</strong>
                <span><b>{{ $order->user->name }}</b><small>{{ $order->user->email }}</small></span>
                <span>{{ $order->created_at->format('M j, Y, g:i A') }}<small>Pickup {{ $order->pickup_time->format('M j, g:i A') }}</small></span>
                <span><span class="manage-status status-{{ $order->status }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span><small class="manage-status status-{{ $order->refund_status }}">Refund: {{ ucfirst($order->refund_status) }}@if($order->refund_amount) · <x-money :amount="$order->refund_amount" />@endif</small></span>
                <strong class="admin-order-total"><small>ORDER TOTAL</small><span><x-money :amount="$order->total" /></span></strong>
            </a>
        @empty
            <p class="manage-empty">No orders match these filters.</p>
        @endforelse
        {{ $orders->links() }}
    </div>
</section>
@endsection
