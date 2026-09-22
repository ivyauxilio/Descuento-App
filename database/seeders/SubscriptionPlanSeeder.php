<?php
// database/seeders/SubscriptionPlanSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    public function run()
    {
        $plans = [
            [
                'name' => 'Starter Boost',
                'slug' => 'starter-boost',
                'description' => 'Perfect for getting started with promotions',
                'price' => 299,
                'base_credits' => 75,
                'bonus_credits' => 5,
                'icon' => 'rocket',
                'tagline' => 'GREAT VALUE',
                'star_rating' => 3,
                'sort_order' => 1,
                'is_popular' => false,
                'features' => [
                    'Basic Voucher support',
                    'Standard placement',
                    'Email support',
                ],
            ],
            [
                'name' => 'Growth Boost',
                'slug' => 'growth-boost',
                'description' => 'Best for growing businesses',
                'price' => 499,
                'base_credits' => 150,
                'bonus_credits' => 10,
                'icon' => 'chart',
                'tagline' => 'BETTER VALUE',
                'star_rating' => 4,
                'sort_order' => 2,
                'is_popular' => true,
                'features' => [
                    'Basic + Featured Vouchers',
                    'Highlighted placement',
                    'Priority support',
                ],
            ],
            [
                'name' => 'Pro Boost',
                'slug' => 'pro-boost',
                'description' => 'For serious merchants',
                'price' => 999,
                'base_credits' => 350,
                'bonus_credits' => 25,
                'icon' => 'target',
                'tagline' => 'BEST VALUE',
                'star_rating' => 5,
                'sort_order' => 3,
                'is_popular' => false,
                'features' => [
                    'All voucher types',
                    'Boosted placement',
                    'Priority support',
                    'Advanced analytics',
                ],
            ],
            [
                'name' => 'Premium Boost',
                'slug' => 'premium-boost',
                'description' => 'Maximum marketing power',
                'price' => 1999,
                'base_credits' => 800,
                'bonus_credits' => 50,
                'icon' => 'crown',
                'badge' => 'MAXIMUM VALUE',
                'tagline' => 'MAXIMUM VALUE',
                'star_rating' => 5,
                'sort_order' => 4,
                'is_popular' => false,
                'features' => [
                    'All voucher types',
                    'Top platform placement',
                    'Dedicated account manager',
                    'Custom promotion campaigns',
                    'Real-time analytics',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('✅ Subscription plans seeded!');
    }
}