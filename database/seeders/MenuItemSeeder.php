<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Merchant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $merchants = Merchant::all();

        foreach ($merchants as $merchant) {
            $items = [
                ['name' => 'Classic Burger', 'description' => 'Juicy beef patty with lettuce, tomato, and cheese', 'price' => 250.00],
                ['name' => 'Cheese Pizza', 'description' => 'Classic pizza with mozzarella and tomato sauce', 'price' => 350.00],
                ['name' => 'Caesar Salad', 'description' => 'Fresh romaine lettuce with Caesar dressing', 'price' => 180.00],
                ['name' => 'Iced Tea', 'description' => 'Refreshing iced tea with lemon', 'price' => 80.00],
                ['name' => 'French Fries', 'description' => 'Crispy golden fries with special seasoning', 'price' => 120.00],
                ['name' => 'Chicken Sandwich', 'description' => 'Grilled chicken with avocado and mayo', 'price' => 280.00],
                ['name' => 'Spaghetti Carbonara', 'description' => 'Classic Italian pasta with creamy sauce', 'price' => 320.00],
                ['name' => 'Mango Shake', 'description' => 'Fresh mango blended with milk and ice', 'price' => 150.00],
                ['name' => 'Buffalo Wings', 'description' => 'Spicy chicken wings with blue cheese dip', 'price' => 290.00],
                ['name' => 'Chocolate Cake', 'description' => 'Rich chocolate cake with ganache', 'price' => 190.00],
            ];

            foreach ($items as $item) {
                MenuItem::create([
                    'menu_item_id' => Str::uuid(),
                    'merchant_id' => $merchant->merchant_id,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'image_url' => null,
                    'status' => 'available',
                ]);
            }
        }

        $this->command->info('Menu items seeded successfully!');
    }
}