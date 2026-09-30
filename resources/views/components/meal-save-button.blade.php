@props(['food', 'isSaved' => false])

@if(Auth::check() && Auth::user()->role === 'customer')
    <form class="meal-save-form" method="POST" action="{{ $isSaved ? route('favorites.destroy', $food) : route('favorites.store', $food) }}">
        @csrf
        @if($isSaved)
            @method('DELETE')
        @endif
        <button class="meal-save-button {{ $isSaved ? 'is-saved' : '' }}" type="submit" aria-label="{{ $isSaved ? 'Remove '.$food->name.' from saved meals' : 'Save '.$food->name.' for later' }}" title="{{ $isSaved ? 'Remove from saved meals' : 'Save for later' }}">
            <span aria-hidden="true">@if($isSaved)&#9829;@else&#9825;@endif</span>
        </button>
    </form>
@elseif(!Auth::check())
    <a class="meal-save-button meal-save-link" href="{{ route('login') }}" aria-label="Sign in to save {{ $food->name }}" title="Sign in to save this meal">
        <span aria-hidden="true">&#9825;</span>
    </a>
@endif
