@extends('layouts.app')
@section('title', $food->exists ? 'Edit meal' : 'Add a meal')
@section('content')
<section class="manage-shell">
    <div class="manage-panel meal-editor">
        <span class="manage-kicker">YOUR MENU</span>
        <h1>{{ $food->exists ? 'Update meal' : 'Add a meal' }}</h1>
        <form class="manage-form" method="POST" enctype="multipart/form-data" action="{{ $food->exists ? route('vendor.meals.update', $food) : route('vendor.meals.store') }}">
            @csrf
            @if($food->exists)
                @method('PUT')
            @endif
            <label>Meal name<input name="name" value="{{ old('name', $food->name) }}" required></label>
            <label>Description<textarea name="description" rows="4">{{ old('description', $food->description) }}</textarea></label>
            <label>Price in Ghana cedis<input type="number" name="price" step="0.01" min="0.01" value="{{ old('price', $food->price) }}" required></label>
            <label>Preparation time in minutes<input type="number" name="preparation_minutes" min="1" max="240" value="{{ old('preparation_minutes', $food->preparation_minutes ?? 15) }}" required></label>
            <label for="meal-image-input">Meal photo <span class="optional">JPG, PNG or WebP · max 2 MB</span></label>
            <input id="meal-image-input" type="file" name="image" accept="image/jpeg,image/png,image/webp">
            <img
                class="meal-edit-preview"
                id="meal-image-preview"
                src="{{ $food->image_url ?: '/images/meal-placeholder.svg' }}"
                alt="{{ $food->exists ? 'Current '.$food->name.' photo' : 'Meal photo preview' }}"
                onerror="this.onerror=null;this.src='/images/meal-placeholder.svg'"
            >
            <label class="manage-checkbox"><input type="checkbox" name="available" value="1" @checked(old('available', $food->exists ? $food->available : true))> Show this meal as available to customers</label>
            <button class="manage-button" type="submit">{{ $food->exists ? 'Save meal changes' : 'Add meal to menu' }}</button>
            <a class="manage-text-link" href="{{ route('vendor.meals.index') }}">Cancel</a>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (() => {
        const input = document.getElementById('meal-image-input');
        const preview = document.getElementById('meal-image-preview');
        let previewUrl = null;

        input?.addEventListener('change', () => {
            const image = input.files?.[0];
            if (!image) {
                return;
            }

            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }

            previewUrl = URL.createObjectURL(image);
            preview.src = previewUrl;
            preview.alt = `Selected meal photo: ${image.name}`;
        });

        window.addEventListener('pagehide', () => {
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }
        });
    })();
</script>
@endpush
