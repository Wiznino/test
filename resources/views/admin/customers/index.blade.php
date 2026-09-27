@extends('layouts.app')
@section('title', 'Customers')
@section('content')
<section class="manage-shell"><div class="manage-title page-manage-title"><div><span class="manage-kicker">ADMINISTRATION · CUSTOMER DIRECTORY</span><h1>Customer <em>information</em></h1><p>Find customer contact details and open their order history.</p></div><a class="manage-outline" href="{{ route('admin.dashboard') }}">← Overview</a></div>
<form class="admin-search" method="GET" action="{{ route('admin.customers.index') }}"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search name, email, or phone"><button class="manage-button" type="submit">Search</button>@if(request('q'))<a class="manage-text-link" href="{{ route('admin.customers.index') }}">Clear</a>@endif</form>
<div class="manage-panel admin-record-list"><div class="admin-record-heading"><span>Customer</span><span>Contact</span><span>Orders</span><span>Joined</span></div>
@forelse($customers as $customer)<a class="admin-record-row" href="{{ route('admin.customers.show', $customer) }}"><span class="admin-record-person"><span class="vendor-avatar">{{ strtoupper(substr($customer->name, 0, 1)) }}</span><strong>{{ $customer->name }}</strong></span><span><b>{{ $customer->email }}</b><small>{{ $customer->phone ?: 'No phone provided' }}</small></span><span class="admin-record-number">{{ $customer->orders_count }}</span><span>{{ $customer->created_at->format('M j, Y') }} <b class="admin-row-arrow">›</b></span></a>@empty<p class="manage-empty">No customers found.</p>@endforelse
{{ $customers->links() }}</div></section>
@endsection
