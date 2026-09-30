<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FoodFavoriteController extends Controller
{
    public function index(): View
    {
        $foods = Auth::user()->favoriteFoods()
            ->with('vendor')
            ->withCount('reviews')
            ->withAvg('reviews as portion_rating_average', 'portion_rating')
            ->withAvg('reviews as value_rating_average', 'value_rating')
            ->withAvg('reviews as accuracy_rating_average', 'accuracy_rating')
            ->orderBy('foods.name')
            ->get();

        return view('favorites.index', compact('foods'));
    }

    public function store(Food $food): RedirectResponse
    {
        Auth::user()->favoriteFoods()->syncWithoutDetaching([$food->id]);

        return back()->with('success', 'Meal saved to your favourites.');
    }

    public function destroy(Food $food): RedirectResponse
    {
        Auth::user()->favoriteFoods()->detach($food->id);

        return back()->with('success', 'Meal removed from your saved meals.');
    }
}
