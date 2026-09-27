@extends('layouts.app')
@section('title', 'Vendor dashboard')
@section('content')
<section class="manage-shell"><div class="manage-hero"><div><span class="manage-kicker">VENDOR WORKSPACE</span><h1>Welcome, <em>{{ Auth::user()->name }}</em></h1><p>Your menu and orders, all in one place.</p><div class="manage-actions"><a class="manage-button" href="{{ route('vendor.meals.create') }}">＋ Add a meal</a><a class="manage-outline" href="{{ route('vendor.orders.index') }}">View incoming orders</a></div></div></div>
<div class="manage-stats"><article><small>Your meals</small><strong>{{ $stats['meals'] }}</strong></article><article><small>Available</small><strong>{{ $stats['available'] }}</strong></article><article><small>Orders received</small><strong>{{ $stats['orders'] }}</strong></article><article><small>Ready or completed sales</small><strong>GH&#8373; {{ number_format($stats['sales'], 2) }}</strong></article></div>
<section class="manage-panel"><div class="manage-title"><div><span class="manage-kicker">LATEST ACTIVITY</span><h2>Recent order items</h2></div><a class="manage-text-link" href="{{ route('vendor.orders.index') }}">All orders →</a></div>
@forelse($recentItems as $item)<div class="vendor-row"><div class="vendor-ident"><span class="vendor-avatar">#{{ $item->order_id }}</span><div><strong>{{ $item->food_name }} × {{ $item->quantity }}</strong><small>{{ $item->order->user->name }} · {{ $item->created_at->format('M j, g:i A') }}</small></div></div><span class="manage-status status-{{ $item->status }}">{{ ucfirst($item->status) }}</span><strong>GH&#8373; {{ number_format($item->price * $item->quantity, 2) }}</strong></div>@empty<p class="manage-empty">No orders yet. Your meals will appear in customer menus once available.</p>@endforelse
</section></section>
@endsection
