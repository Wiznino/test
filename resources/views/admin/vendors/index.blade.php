@extends('layouts.app')
@section('title', 'Vendors')
@section('content')
<section class="manage-shell"><div class="manage-title page-manage-title"><div><span class="manage-kicker">ADMINISTRATION · SUPPLY PARTNERS</span><h1>Vendor <em>performance</em></h1><p>Review vendor contact information, menu size, order volume, and sales.</p></div><a class="manage-outline admin-blue-outline" href="{{ route('admin.dashboard') }}#vendor-form">＋ Add vendor</a></div>
<div class="manage-panel admin-record-list"><div class="admin-vendor-heading"><span>Vendor</span><span>Menu</span><span>Orders</span><span>Meals sold</span><span>Sales</span><span>Access</span></div>
@forelse($vendors as $vendor)@php($result = $performance->get($vendor->id))<a class="admin-vendor-row" href="{{ route('admin.vendors.show', $vendor) }}"><span class="admin-record-person"><span class="vendor-avatar">{{ strtoupper(substr($vendor->name, 0, 1)) }}</span><span><strong>{{ $vendor->name }}</strong><small>{{ str_ends_with($vendor->email, '@atu-eats.local') ? ($vendor->phone ?: 'Legacy phone login') : $vendor->email }}</small></span></span><span>{{ $vendor->foods_count }} meals</span><span>{{ $result->orders_count ?? 0 }}</span><span>{{ $result->meals_sold ?? 0 }}</span><strong>GH&#8373; {{ number_format($result->revenue ?? 0, 2) }}</strong><span class="manage-status {{ $vendor->is_active ? 'status-ready' : 'status-received' }}">{{ $vendor->is_active ? 'Active' : 'Paused' }}</span></a>@empty<p class="manage-empty">No vendors have been added yet.</p>@endforelse
{{ $vendors->links() }}</div></section>
@endsection
