@extends('layouts.app')
@section('title', 'Admin dashboard')
@section('content')
<section class="manage-shell">
    <div class="manage-hero"><div><span class="manage-kicker">ATU EATS · ADMINISTRATION</span><h1>Campus <em>overview</em></h1><p>Manage vendor access and keep an eye on the marketplace.</p></div></div>
    <div class="manage-stats"><a class="manage-stat-link" href="{{ route('admin.customers.index') }}"><article><small>Customers · view details →</small><strong>{{ number_format($stats['users']) }}</strong></article></a><a class="manage-stat-link" href="{{ route('admin.vendors.index') }}"><article><small>Vendors · view performance →</small><strong>{{ number_format($stats['vendors']) }}</strong><span>{{ $stats['active_vendors'] }} active</span></article></a><a class="manage-stat-link" href="{{ route('admin.orders.index') }}"><article><small>Orders · view orders →</small><strong>{{ number_format($stats['orders']) }}</strong></article></a><a class="manage-stat-link" href="{{ route('admin.orders.index') }}"><article><small>Completed sales · order details →</small><strong>GH&#8373; {{ number_format($stats['sales'], 2) }}</strong></article></a></div>
    <div class="manage-grid">
        <section class="manage-panel"><div class="manage-title"><div><span class="manage-kicker">GROW THE MARKETPLACE</span><h2>Add a vendor</h2></div></div>
            <form class="manage-form" id="vendor-form" method="POST" action="{{ route('admin.vendors.store') }}">@csrf
                <label>Business or vendor name<input name="name" value="{{ old('name') }}" required></label><label>Phone number <span class="optional">Used to sign in</span><input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" required></label><label>Temporary password<input type="password" name="password" minlength="8" required><small>At least 8 characters. Share privately with the vendor.</small></label><label>Confirm password<input type="password" name="password_confirmation" required></label><button class="manage-button" type="submit">Create vendor account</button>
            </form>
        </section>
        <section class="manage-panel" id="vendors"><div class="manage-title"><div><span class="manage-kicker">SUPPLY PARTNERS</span><h2>Vendor performance</h2></div></div>
            @forelse($vendors as $vendor)@php($result = $performance->get($vendor->id))<div class="vendor-row"><div class="vendor-ident"><span class="vendor-avatar">{{ strtoupper(substr($vendor->name,0,1)) }}</span><div><a class="manage-text-link" href="{{ route('admin.vendors.show', $vendor) }}">{{ $vendor->name }} →</a><small>{{ $vendor->phone }} · {{ $vendor->foods_count }} meals</small></div></div><div class="vendor-metrics"><span>{{ $result->orders_count ?? 0 }} orders</span><span>{{ $result->meals_sold ?? 0 }} sold</span><strong>GH&#8373; {{ number_format($result->revenue ?? 0, 2) }}</strong></div><form method="POST" action="{{ route('admin.vendors.status', $vendor) }}">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $vendor->is_active ? 0 : 1 }}"><button class="manage-small-button" type="submit">{{ $vendor->is_active ? 'Pause access' : 'Activate' }}</button></form></div>@empty<p class="manage-empty">No vendors yet. Add your first vendor using the form.</p>@endforelse
        </section>
    </div>
</section>
@endsection
