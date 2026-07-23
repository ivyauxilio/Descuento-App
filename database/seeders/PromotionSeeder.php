<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\MenuItem;
use App\Models\Promotion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        // Get all merchants with their menu items
        $merchants = Merchant::with('menuItems')->get();

        foreach ($merchants as $merchant) {
            $menuItems = $merchant->menuItems;
            $itemIds = $menuItems->pluck('menu_item_id')->toArray();

            // Skip if no menu items
            if (empty($itemIds)) {
                continue;
            }

            // Get random items for promotions
            $randomItem1 = $menuItems->random()->menu_item_id ?? null;
            $randomItem2 = $menuItems->random()->menu_item_id ?? null;
            $randomItem3 = $menuItems->random()->menu_item_id ?? null;

            // 1. Percentage Discount (20% off)
            Promotion::create([
                'promotion_id' => Str::uuid(),
                'merchant_id' => $merchant->merchant_id,
                'category_id' => null,
                'free_menu_item_id' => null,
                'required_menu_item_id' => null,
                'title' => '20% Off Everything!',
                'promo_type' => 'percentage',
                'value' => 20.00,
                'min_order_amount' => 100.00,
                'min_quantity' => null,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(30)->toDateString(),
                'status' => 'active',
            ]);

            // 2. Fixed Amount Off (₱100 off)
            Promotion::create([
                'promotion_id' => Str::uuid(),
                'merchant_id' => $merchant->merchant_id,
                'category_id' => null,
                'free_menu_item_id' => null,
                'required_menu_item_id' => null,
                'title' => '₱100 Off Your Order',
                'promo_type' => 'fixed',
                'value' => 100.00,
                'min_order_amount' => 500.00,
                'min_quantity' => null,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(45)->toDateString(),
                'status' => 'active',
            ]);

            // 3. BOGO (Buy One Get One Free)
            if ($randomItem1 && $randomItem2) {
                Promotion::create([
                    'promotion_id' => Str::uuid(),
                    'merchant_id' => $merchant->merchant_id,
                    'category_id' => null,
                    'free_menu_item_id' => $randomItem2,
                    'required_menu_item_id' => $randomItem1,
                    'title' => 'Buy 1 Get 1 Free!',
                    'promo_type' => 'bogo',
                    'value' => 0.00,
                    'min_order_amount' => null,
                    'min_quantity' => 2,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addDays(30)->toDateString(),
                    'status' => 'active',
                ]);
            }

            // 4. Free Gift (Get free item with purchase)
            if ($randomItem3) {
                Promotion::create([
                    'promotion_id' => Str::uuid(),
                    'merchant_id' => $merchant->merchant_id,
                    'category_id' => null,
                    'free_menu_item_id' => $randomItem3,
                    'required_menu_item_id' => null,
                    'title' => 'Free Gift with ₱1000 Purchase',
                    'promo_type' => 'bogo',
                    'value' => 0.00,
                    'min_order_amount' => 1000.00,
                    'min_quantity' => null,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addDays(60)->toDateString(),
                    'status' => 'active',
                ]);
            }

            // 5. Bundle Deal (Buy 2, Get 3rd at 50%)
            if (count($itemIds) >= 3) {
                $bundleItems = $menuItems->random(3);
                $bundleItemIds = $bundleItems->pluck('menu_item_id')->toArray();
                
                // Create a bundle promotion (using category_id to store bundle items)
                Promotion::create([
                    'promotion_id' => Str::uuid(),
                    'merchant_id' => $merchant->merchant_id,
                    'category_id' => null,
                    'free_menu_item_id' => null,
                    'required_menu_item_id' => $bundleItemIds[0] ?? null,
                    'title' => 'Buy 2 Get 3rd at 50% Off',
                    'promo_type' => 'percentage',
                    'value' => 50.00,
                    'min_order_amount' => null,
                    'min_quantity' => 3,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addDays(20)->toDateString(),
                    'status' => 'active',
                ]);
            }

            // 6. Tiered Discount (Different discounts based on amount)
            // Create multiple promotions for tiered discounts
            $tiers = [
                ['min' => 1000, 'discount' => 100, 'title' => '₱100 off ₱1000+'],
                ['min' => 2000, 'discount' => 300, 'title' => '₱300 off ₱2000+'],
                ['min' => 5000, 'discount' => 1000, 'title' => '₱1000 off ₱5000+'],
            ];

            foreach ($tiers as $tier) {
                Promotion::create([
                    'promotion_id' => Str::uuid(),
                    'merchant_id' => $merchant->merchant_id,
                    'category_id' => null,
                    'free_menu_item_id' => null,
                    'required_menu_item_id' => null,
                    'title' => $tier['title'],
                    'promo_type' => 'fixed',
                    'value' => $tier['discount'],
                    'min_order_amount' => $tier['min'],
                    'min_quantity' => null,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addDays(90)->toDateString(),
                    'status' => 'active',
                ]);
            }

            // 7. Free Shipping (Not directly in schema, but we can use fixed with special handling)
            Promotion::create([
                'promotion_id' => Str::uuid(),
                'merchant_id' => $merchant->merchant_id,
                'category_id' => null,
                'free_menu_item_id' => null,
                'required_menu_item_id' => null,
                'title' => 'Free Shipping on ₱500+ Orders',
                'promo_type' => 'fixed',
                'value' => 0.00,
                'min_order_amount' => 500.00,
                'min_quantity' => null,
                'start_date' => now()->toDateString(),
                'end_date' => null,
                'status' => 'active',
            ]);

            // 8. Loyalty Points (Earn points per purchase)
            // Note: This is tracked separately, but we create a promotion entry
            Promotion::create([
                'promotion_id' => Str::uuid(),
                'merchant_id' => $merchant->merchant_id,
                'category_id' => null,
                'free_menu_item_id' => null,
                'required_menu_item_id' => null,
                'title' => 'Earn 10 Points per ₱100 Spent',
                'promo_type' => 'percentage',
                'value' => 10.00,
                'min_order_amount' => 100.00,
                'min_quantity' => null,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(365)->toDateString(),
                'status' => 'active',
            ]);

            // 9. Inactive Promotions (for testing)
            Promotion::create([
                'promotion_id' => Str::uuid(),
                'merchant_id' => $merchant->merchant_id,
                'category_id' => null,
                'free_menu_item_id' => null,
                'required_menu_item_id' => null,
                'title' => '15% Off (Inactive)',
                'promo_type' => 'percentage',
                'value' => 15.00,
                'min_order_amount' => 200.00,
                'min_quantity' => null,
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->subDays(5)->toDateString(),
                'status' => 'expired',
            ]);

            // 10. Flash Sale (Active for short period)
            Promotion::create([
                'promotion_id' => Str::uuid(),
                'merchant_id' => $merchant->merchant_id,
                'category_id' => null,
                'free_menu_item_id' => null,
                'required_menu_item_id' => null,
                'title' => 'Flash Sale: 30% Off!',
                'promo_type' => 'percentage',
                'value' => 30.00,
                'min_order_amount' => 300.00,
                'min_quantity' => null,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(2)->toDateString(),
                'status' => 'active',
            ]);

            // 11. First Purchase Discount
            Promotion::create([
                'promotion_id' => Str::uuid(),
                'merchant_id' => $merchant->merchant_id,
                'category_id' => null,
                'free_menu_item_id' => null,
                'required_menu_item_id' => null,
                'title' => 'Welcome! 15% Off First Purchase',
                'promo_type' => 'percentage',
                'value' => 15.00,
                'min_order_amount' => 100.00,
                'min_quantity' => null,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(60)->toDateString(),
                'status' => 'active',
            ]);

            $this->command->info("Promotions created for merchant: {$merchant->business_name}");
        }

        $this->command->info('All promotions seeded successfully!');
    }
}