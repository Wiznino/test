<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MealController extends Controller
{
    public function index(): View
    {
        $foods = Auth::user()->foods()->latest()->paginate(12);

        return view('vendor.meals.index', compact('foods'));
    }

    public function create(): View
    {
        return view('vendor.meals.form', ['food' => new Food]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('meals', 'public');
        }
        $data['available'] = $request->boolean('available');
        Auth::user()->foods()->create($data);

        return redirect()->route('vendor.meals.index')->with('success', 'Meal added to your menu.');
    }

    public function edit(Food $food): View
    {
        $this->authorizeFood($food);

        return view('vendor.meals.form', compact('food'));
    }

    public function update(Request $request, Food $food): RedirectResponse
    {
        $this->authorizeFood($food);
        $data = $this->validated($request);
        if ($request->hasFile('image')) {
            if ($food->image && ! str_starts_with($food->image, 'http')) {
                Storage::disk('public')->delete($food->image);
            }
            $data['image'] = $request->file('image')->store('meals', 'public');
        }
        $data['available'] = $request->boolean('available');
        $food->update($data);

        return redirect()->route('vendor.meals.index')->with('success', 'Meal updated.');
    }

    public function destroy(Food $food): RedirectResponse
    {
        $this->authorizeFood($food);
        if ($food->image && ! str_starts_with($food->image, 'http')) {
            Storage::disk('public')->delete($food->image);
        }
        $food->delete();

        return back()->with('success', 'Meal removed from your menu.');
    }

    private function validated(Request $request): array
    {
        return $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string|max:2000', 'price' => 'required|numeric|min:0.01|max:99999.99', 'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048']);
    }

    private function authorizeFood(Food $food): void
    {
        abort_unless($food->vendor_id === Auth::id(), 404);
    }
}
