@extends('layouts.app')
@section('title', 'Manage promotions')
@section('content')
<section class="manage-shell">
    <div class="manage-hero"><div><span class="manage-kicker">CUSTOMER MESSAGES</span><h1 class="promotion-page-title">Promotions &amp; offers</h1><p>Post campus offers to the home page, menu, and customer dashboard.</p></div></div>
    <div class="promotion-admin-layout">
        <section class="manage-panel"><div class="manage-title"><div><span class="manage-kicker">CREATE A CAMPAIGN</span><h2>New promotion</h2></div></div>
            <form class="manage-form" method="POST" action="{{ route('admin.promotions.store') }}" enctype="multipart/form-data">@csrf
                <label>Promotion title<input name="title" value="{{ old('title') }}" maxlength="120" required></label>
                <label>Details<textarea name="description" rows="4" maxlength="1000" required>{{ old('description') }}</textarea></label>
                <label>Banner image <span class="optional">JPG, PNG or WebP, up to 5 MB</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
                <div class="promotion-date-fields"><label>Starts <span class="optional">Optional</span><input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"></label><label>Ends <span class="optional">Optional</span><input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}"></label></div>
                <input type="hidden" name="is_active" value="0"><label class="manage-checkbox"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') === '1')> Publish when saved</label>
                <button class="manage-button" type="submit">Publish promotion</button>
            </form>
        </section>
        <section class="manage-panel"><div class="manage-title"><div><span class="manage-kicker">PROMOTION LIBRARY</span><h2>Manage offers</h2></div></div>
            @forelse($promotions as $promotion)
                <article class="promotion-admin-card">
                    <div class="promotion-admin-heading">@if($promotion->image_url)<img src="{{ $promotion->image_url }}" alt="">@endif<div><span class="manage-status {{ $promotion->is_active ? 'status-ready' : 'status-received' }}">{{ $promotion->is_active ? 'Published' : 'Hidden' }}</span><h3>{{ $promotion->title }}</h3><small>{{ $promotion->starts_at?->format('M j, Y g:i A') ?? 'No start date' }} — {{ $promotion->ends_at?->format('M j, Y g:i A') ?? 'No end date' }}</small></div></div>
                    <form class="manage-form promotion-edit-form" method="POST" action="{{ route('admin.promotions.update', $promotion) }}" enctype="multipart/form-data">@csrf @method('PUT')
                        <label>Title<input name="title" value="{{ $promotion->title }}" maxlength="120" required></label><label>Details<textarea name="description" rows="3" maxlength="1000" required>{{ $promotion->description }}</textarea></label>
                        <label>Replace banner <span class="optional">Leave empty to keep current image</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
                        <div class="promotion-date-fields"><label>Starts<input type="datetime-local" name="starts_at" value="{{ $promotion->starts_at?->format('Y-m-d\TH:i') }}"></label><label>Ends<input type="datetime-local" name="ends_at" value="{{ $promotion->ends_at?->format('Y-m-d\TH:i') }}"></label></div>
                        <input type="hidden" name="is_active" value="0"><label class="manage-checkbox"><input type="checkbox" name="is_active" value="1" @checked($promotion->is_active)> Publish to customers</label><button class="manage-button" type="submit">Save changes</button>
                    </form>
                    <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}" onsubmit="return confirm('Delete this promotion?')">@csrf @method('DELETE')<button class="manage-danger" type="submit">Delete promotion</button></form>
                </article>
            @empty
                <p class="manage-empty">No promotions yet. Create your first offer to share it with customers.</p>
            @endforelse
            {{ $promotions->links() }}
        </section>
    </div>
</section>
@endsection
