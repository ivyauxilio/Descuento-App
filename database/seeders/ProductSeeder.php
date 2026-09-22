<?php
// database/seeders/ProductSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\ProductPoints;
use App\Models\User;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Create a test merchant with user
        $user = User::factory()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'merchant@example.com',
            'phone' => '09171234567',
            'password' => bcrypt('password123'),
            'role' => 'merchant',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $merchant = Merchant::factory()
            ->for($user)
            ->create([
                'business_name' => 'FreshMart Grocery',
                'business_type' => 'Grocery Store',
                'business_email' => 'freshmart@example.com',
                'is_verified' => true,
                'is_active' => true,
                'is_approved' => true,
            ]);

        // Create a customer user
        User::factory()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'email' => 'customer@example.com',
            'phone' => '09176543210',
            'password' => bcrypt('password123'),
            'role' => 'customer',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create an admin user
        User::factory()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => 'admin@example.com',
            'phone' => '09170000000',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create products with discounts and points
        $categories = ['Fruits', 'Vegetables', 'Meat', 'Dairy', 'Bakery', 'Beverages', 'Snacks'];

        foreach ($categories as $category) {
            // Create 5 products per category
            for ($i = 1; $i <= 5; $i++) {
                $product = Product::factory()
                    ->for($merchant)
                    ->create([
                        'category' => $category,
                        'is_active' => true,
                        'in_stock' => true,
                        'stock_quantity' => rand(20, 500),
                    ]);

                // Add discount to some products (30% chance)
                if (rand(1, 10) <= 3) {
                    ProductDiscount::factory()
                        ->for($product)
                        ->for($merchant)
                        ->active()
                        ->create([
                            'type' => rand(1, 2) === 1 ? 'percentage' : 'fixed',
                            'value' => rand(1, 2) === 1 ? rand(10, 30) : rand(50, 200),
                        ]);
                }

                // Add points to all products
                ProductPoints::factory()
                    ->for($product)
                    ->for($merchant)
                    ->active()
                    ->create([
                        'points_per_item' => rand(1, 20),
                        'points_per_php' => rand(0, 5),
                    ]);
            }
        }

        // Create some featured products
        Product::where('merchant_id', $merchant->merchant_id)
            ->inRandomOrder()
            ->limit(5)
            ->update(['is_featured' => true]);

        // Create some low stock products
        Product::where('merchant_id', $merchant->merchant_id)
            ->inRandomOrder()
            ->limit(3)
            ->update([
                'stock_quantity' => rand(1, 5),
                'min_stock_alert' => rand(5, 10),
            ]);

        // Create some out of stock products
        Product::where('merchant_id', $merchant->merchant_id)
            ->inRandomOrder()
            ->limit(2)
            ->update([
                'stock_quantity' => 0,
                'in_stock' => false,
            ]);

        $this->command->info('Products seeded successfully!');
        $this->command->info('Test Accounts:');
        $this->command->info('Merchant: merchant@example.com / password123');
        $this->command->info('Customer: customer@example.com / password123');
        $this->command->info('Admin: admin@example.com / password123');
    }
}