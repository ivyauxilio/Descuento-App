<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // $this->call([
        //     UserSeeder::class,        // Create users (admin, merchant, customer)
        //     CategorySeeder::class,    // Create categories
        //     ProvinceSeeder::class,    // Create provinces
        //     MerchantSeeder::class,    // Create merchants
            //  MenuItemSeeder::class,      // Create menu items first
            // PromotionSeeder::class,     // Then promotions
        // ]);
    }
}