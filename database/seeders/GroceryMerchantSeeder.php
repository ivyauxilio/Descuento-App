<?php
// database/seeders/GroceryMerchantSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\ProductPoints;
use App\Models\User;
use App\Models\Category;
use App\Models\Province;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class GroceryMerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Check if grocery merchant already exists
        $existingUser = User::where('email', 'grocery@example.com')->first();
        
        if ($existingUser) {
            $this->command->info('Grocery merchant already exists!');
            return;
        }

        // Get or create a category for grocery
        $category = Category::where('name', 'Grocery')->first();
        if (!$category) {
            $category = Category::create([
                'category_id' => (string) Str::uuid(),
                'name' => 'Grocery',
                'description' => 'Grocery stores and supermarkets',
            ]);
            $this->command->info('Created category: Grocery');
        }

        // Get or create a province
        $province = Province::where('name', 'Metro Manila')->first();
        if (!$province) {
            $province = Province::create([
                'province_id' => (string) Str::uuid(),
                'name' => 'Metro Manila',
                'region' => 'National Capital Region',
            ]);
            $this->command->info('Created province: Metro Manila');
        }

        // Create a new user for the grocery merchant
        $user = User::create([
            'uuid' => (string) Str::uuid(),
            'firstname' => 'Grocery',
            'lastname' => 'Mart',
            'email' => 'grocery@example.com',
            'phone' => '09171234567',
            'password' => Hash::make('password123'),
            'role' => 'merchant',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Get admin user for approval
        $admin = User::where('role', 'admin')->first();

        // Create the merchant profile matching your table schema
        $merchant = Merchant::create([
            'merchant_id' => (string) Str::uuid(),
            'owner_id' => $user->id,
            'category_id' => $category->category_id,
            'province_id' => $province->province_id,
            'approved_by' => $admin ? $admin->id : null,
            'business_name' => 'FreshMart Grocery Store',
            'branch_name' => 'Main Branch',
            'email' => 'grocery@example.com',
            'street_address' => '123 Main Street, Barangay Central',
            'city' => 'Manila',
            'status' => 'active', // pending, approved, active, rejected, suspended
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Grocery merchant created successfully!');
        $this->command->info('Email: grocery@example.com');
        $this->command->info('Password: password123');

        // Create grocery products
        $this->createGroceryProducts($merchant);

        $this->command->info('Grocery products seeded successfully!');
    }

    /**
     * Create grocery products for the merchant
     */
    private function createGroceryProducts($merchant)
    {
        // Categories with their products
        $categories = [
            'Fruits' => [
                ['name' => 'Apple', 'price' => 120, 'unit' => 'kg'],
                ['name' => 'Banana', 'price' => 80, 'unit' => 'kg'],
                ['name' => 'Orange', 'price' => 150, 'unit' => 'kg'],
                ['name' => 'Mango', 'price' => 180, 'unit' => 'kg'],
                ['name' => 'Grapes', 'price' => 250, 'unit' => 'kg'],
                ['name' => 'Strawberry', 'price' => 350, 'unit' => 'box'],
                ['name' => 'Watermelon', 'price' => 200, 'unit' => 'piece'],
                ['name' => 'Pineapple', 'price' => 120, 'unit' => 'piece'],
            ],
            'Vegetables' => [
                ['name' => 'Cabbage', 'price' => 60, 'unit' => 'kg'],
                ['name' => 'Carrot', 'price' => 80, 'unit' => 'kg'],
                ['name' => 'Tomato', 'price' => 70, 'unit' => 'kg'],
                ['name' => 'Onion', 'price' => 90, 'unit' => 'kg'],
                ['name' => 'Garlic', 'price' => 120, 'unit' => 'kg'],
                ['name' => 'Broccoli', 'price' => 150, 'unit' => 'kg'],
                ['name' => 'Bell Pepper', 'price' => 130, 'unit' => 'kg'],
                ['name' => 'Potato', 'price' => 85, 'unit' => 'kg'],
            ],
            'Meat' => [
                ['name' => 'Chicken Breast', 'price' => 240, 'unit' => 'kg'],
                ['name' => 'Pork Belly', 'price' => 320, 'unit' => 'kg'],
                ['name' => 'Beef Tenderloin', 'price' => 550, 'unit' => 'kg'],
                ['name' => 'Chicken Thigh', 'price' => 200, 'unit' => 'kg'],
                ['name' => 'Pork Chop', 'price' => 280, 'unit' => 'kg'],
                ['name' => 'Ground Beef', 'price' => 350, 'unit' => 'kg'],
                ['name' => 'Sausage', 'price' => 180, 'unit' => 'pack'],
                ['name' => 'Bacon', 'price' => 250, 'unit' => 'pack'],
            ],
            'Dairy' => [
                ['name' => 'Fresh Milk', 'price' => 90, 'unit' => 'liter'],
                ['name' => 'Cheese', 'price' => 180, 'unit' => 'pack'],
                ['name' => 'Yogurt', 'price' => 60, 'unit' => 'pack'],
                ['name' => 'Butter', 'price' => 120, 'unit' => 'pack'],
                ['name' => 'Cream', 'price' => 150, 'unit' => 'pack'],
                ['name' => 'Eggs', 'price' => 120, 'unit' => 'dozen'],
            ],
            'Bakery' => [
                ['name' => 'White Bread', 'price' => 70, 'unit' => 'loaf'],
                ['name' => 'Whole Wheat Bread', 'price' => 85, 'unit' => 'loaf'],
                ['name' => 'Croissant', 'price' => 60, 'unit' => 'piece'],
                ['name' => 'Pandesal', 'price' => 50, 'unit' => 'pack'],
                ['name' => 'Cake Slice', 'price' => 90, 'unit' => 'piece'],
                ['name' => 'Muffin', 'price' => 55, 'unit' => 'piece'],
            ],
            'Beverages' => [
                ['name' => 'Coca-Cola', 'price' => 50, 'unit' => 'bottle'],
                ['name' => 'Water', 'price' => 20, 'unit' => 'bottle'],
                ['name' => 'Juice', 'price' => 40, 'unit' => 'pack'],
                ['name' => 'Coffee', 'price' => 120, 'unit' => 'pack'],
                ['name' => 'Tea', 'price' => 80, 'unit' => 'pack'],
                ['name' => 'Milk Tea', 'price' => 60, 'unit' => 'bottle'],
            ],
            'Snacks' => [
                ['name' => 'Chips', 'price' => 45, 'unit' => 'pack'],
                ['name' => 'Cookies', 'price' => 55, 'unit' => 'pack'],
                ['name' => 'Nuts', 'price' => 70, 'unit' => 'pack'],
                ['name' => 'Chocolate', 'price' => 65, 'unit' => 'bar'],
                ['name' => 'Candy', 'price' => 30, 'unit' => 'pack'],
                ['name' => 'Popcorn', 'price' => 40, 'unit' => 'pack'],
            ],
        ];

        foreach ($categories as $category => $products) {
            foreach ($products as $productData) {
                $name = $productData['name'];
                $price = $productData['price'];
                
                $product = Product::create([
                    'merchant_id' => $merchant->merchant_id,
                    'name' => $name,
                    'slug' => Str::slug($name) . '-' . Str::random(6),
                    'description' => "Fresh and high-quality {$category} - {$name}. Perfect for your daily needs.",
                    'sku' => strtoupper(Str::random(8)),
                    'barcode' => null,
                    'price' => $price,
                    'original_price' => null,
                    'category' => $category,
                    'sub_category' => null,
                    'brand' => null,
                    'unit' => $productData['unit'] ?? 'piece',
                    'unit_value' => null,
                    'stock_quantity' => rand(50, 500),
                    'min_stock_alert' => rand(10, 30),
                    'in_stock' => true,
                    'is_featured' => rand(1, 10) <= 2,
                    'is_active' => true,
                    'image_url' => null,
                    'images' => null,
                    'attributes' => null,
                    'weight' => rand(1, 5) . 'kg',
                    'nutritional_info' => null,
                    'country_of_origin' => 'Philippines',
                ]);

                // Add discount to some products (20% chance)
                if (rand(1, 5) === 1) {
                    $discountType = rand(1, 2) === 1 ? 'percentage' : 'fixed';
                    $discountValue = $discountType === 'percentage' ? rand(10, 30) : rand(20, 100);
                    
                    ProductDiscount::create([
                        'product_id' => $product->product_id,
                        'merchant_id' => $merchant->merchant_id,
                        'type' => $discountType,
                        'value' => $discountValue,
                        'min_quantity' => 1,
                        'max_quantity' => null,
                        'start_date' => now(),
                        'end_date' => now()->addDays(30),
                        'is_active' => true,
                        'usage_limit' => null,
                        'used_count' => 0,
                    ]);
                }

                // Add points to all products
                ProductPoints::create([
                    'product_id' => $product->product_id,
                    'merchant_id' => $merchant->merchant_id,
                    'points_per_item' => rand(1, 10),
                    'points_per_php' => rand(1, 5),
                    'min_spend' => 0,
                    'max_points' => null,
                    'start_date' => now(),
                    'end_date' => null,
                    'is_active' => true,
                ]);
            }
        }

        // Create featured products
        Product::where('merchant_id', $merchant->merchant_id)
            ->inRandomOrder()
            ->limit(8)
            ->update(['is_featured' => true]);

        // Create some products with additional discounts
        $productsWithDiscount = Product::where('merchant_id', $merchant->merchant_id)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        foreach ($productsWithDiscount as $product) {
            // Create different types of discounts
            $types = ['percentage', 'fixed', 'bogo'];
            $type = $types[array_rand($types)];
            
            $value = match($type) {
                'percentage' => rand(15, 40),
                'fixed' => rand(50, 200),
                'bogo' => 1,
            };

            ProductDiscount::create([
                'product_id' => $product->product_id,
                'merchant_id' => $merchant->merchant_id,
                'type' => $type,
                'value' => $value,
                'min_quantity' => 1,
                'max_quantity' => null,
                'start_date' => now(),
                'end_date' => now()->addDays(60),
                'is_active' => true,
                'usage_limit' => rand(50, 200),
                'used_count' => 0,
            ]);
        }

        // Create some low stock products
        Product::where('merchant_id', $merchant->merchant_id)
            ->inRandomOrder()
            ->limit(3)
            ->update([
                'stock_quantity' => rand(1, 5),
                'min_stock_alert' => 10,
            ]);

        // Create some out of stock products
        Product::where('merchant_id', $merchant->merchant_id)
            ->inRandomOrder()
            ->limit(2)
            ->update([
                'stock_quantity' => 0,
                'in_stock' => false,
            ]);
    }
}