@extends('layouts.app')
@section('title', 'Orders')
@section('content')
<section class="manage-shell"><div class="manage-title page-manage-title"><div><span class="manage-kicker">ADMINISTRATION · ORDER MONITOR</span><h1>All <em>orders</em></h1><p>Open an order to review its customer, meals, vendors, pickup time, and live statuses.</p></div><a class="manage-outline admin-blue-outline" href="{{ route('admin.dashboard') }}">← Overview</a></div>
<form class="admin-search" method="GET" action="{{ route('admin.orders.index') }}"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search order number or customer"><button class="manage-button" type="submit">Search</button>@if(request('q'))<a class="manage-text-link" href="{{ route('admin.orders.index') }}">Clear</a>@endif</form>
<div class="manage-panel admin-record-list"><div class="admin-order-heading"><span>Order</span><span>Customer</span><span>Placed / pickup</span><span>Status</span><span>Total</span></div>
@forelse($orders as $order)<a class="admin-order-list-row" href="{{ route('admin.orders.show', $order) }}"><strong class="admin-order-number">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</strong><span><b>{{ $order->user->name }}</b><small>{{ $order->user->email }}</small></span><span>{{ $order->created_at->format('M j, Y · g:i A') }}<small>Pickup {{ $order->pickup_time->format('M j · g:i A') }}</small></span><span class="manage-status status-{{ $order->status }}">{{ ucfirst($order->status) }}</span><strong class="admin-order-total"><small>ORDER TOTAL</small><span><x-money :amount="$order->total" /></span></strong></a>@empty<p class="manage-empty">No orders found.</p>@endforelse
{{ $orders->links() }}</div></section>
@endsection
