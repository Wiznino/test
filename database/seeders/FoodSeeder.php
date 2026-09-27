<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        $meals = [
            ['Jollof Rice & Chicken', 'Smoky, slow-cooked jollof with grilled chicken and a fresh side.', 35, 'https://images.unsplash.com/photo-1604329760661-e71dc83f8f26?auto=format&fit=crop&w=900&q=85'],
            ['Fried Rice & Chicken', 'Golden wok-fried rice, crisp vegetables and juicy chicken.', 35, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=900&q=85'],
            ['Waakye', 'A Ghanaian favourite with waakye leaves and all the tasty sides.', 30, 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=900&q=85'],
            ['Banku & Tilapia', 'Soft banku, grilled tilapia and bright, spicy pepper sauce.', 45, 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=900&q=85'],
            ['Yam & Chicken', 'Crispy golden yam with seasoned chicken and house sauce.', 32, 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&w=900&q=85'],
            ['Beef Burger', 'A juicy beef patty stacked with greens and our special sauce.', 25, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=900&q=85'],
        ];

        foreach ($meals as [$name, $description, $price, $image]) {
            Food::updateOrCreate(['name' => $name], compact('description', 'price', 'image') + ['available' => true]);
        }
    }
}
