<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Food;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        Food::create([
            'name' => 'Jollof Rice & Chicken',
            'description' => 'Delicious jollof rice served with grilled chicken.',
            'price' => 35.00,
            'image' => null,
            'available' => true,
        ]);

        Food::create([
            'name' => 'Fried Rice & Chicken',
            'description' => 'Fried rice served with crispy chicken.',
            'price' => 35.00,
            'image' => null,
            'available' => true,
        ]);

        Food::create([
            'name' => 'Waakye',
            'description' => 'Traditional Ghanaian waakye served with tasty sides.',
            'price' => 30.00,
            'image' => null,
            'available' => true,
        ]);

        Food::create([
            'name' => 'Banku & Tilapia',
            'description' => 'Soft banku served with grilled tilapia and pepper sauce.',
            'price' => 45.00,
            'image' => null,
            'available' => true,
        ]);

        Food::create([
            'name' => 'Yam & Chicken',
            'description' => 'Fried yam served with seasoned chicken and sauce.',
            'price' => 32.00,
            'image' => null,
            'available' => true,
        ]);

        Food::create([
            'name' => 'Beef Burger',
            'description' => 'Juicy beef burger with vegetables and special sauce.',
            'price' => 25.00,
            'image' => null,
            'available' => true,
        ]);
    }
}
