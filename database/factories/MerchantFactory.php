<?php
// database/factories/MerchantFactory.php

namespace Database\Factories;

use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MerchantFactory extends Factory
{
    protected $model = Merchant::class;

    public function definition()
    {
        // Create a user with merchant role
        $user = User::factory()->merchant()->active()->create();

        $businessTypes = [
            'Grocery Store',
            'Supermarket',
            'Convenience Store',
            'Specialty Store',
            'Wholesale',
            'Organic Market',
            'Meat Shop',
            'Bakery',
            'Dairy Store',
            'Seafood Market',
        ];

        $businessSuffix = ['Mart', 'Store', 'Market', 'Shop', 'Supermarket', 'Grocery'];

        return [
            'merchant_id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'business_name' => $this->faker->company() . ' ' . $this->faker->randomElement($businessSuffix),
            'business_type' => $this->faker->randomElement($businessTypes),
            'business_email' => $this->faker->companyEmail(),
            'business_phone' => $this->faker->phoneNumber(),
            'business_address' => $this->faker->address(),
            'business_city' => $this->faker->city(),
            'business_province' => $this->faker->state(),
            'business_country' => 'Philippines',
            'business_zip' => $this->faker->postcode(),
            'business_logo' => $this->faker->optional()->imageUrl(200, 200, 'business'),
            'business_cover' => $this->faker->optional()->imageUrl(1200, 400, 'business'),
            'tax_id' => $this->faker->optional()->numerify('####-###-####'),
            'registration_number' => $this->faker->optional()->numerify('REG-####-####'),
            'is_verified' => $this->faker->boolean(80),
            'is_active' => $this->faker->boolean(90),
            'is_approved' => $this->faker->boolean(85),
            'verification_documents' => $this->faker->optional()->randomElement([
                ['business_permit.pdf', 'dti_registration.pdf'],
                ['business_permit.pdf', 'sec_registration.pdf'],
                ['business_permit.pdf', 'bir_registration.pdf'],
                null,
            ]),
            'settings' => [
                'order_auto_confirm' => $this->faker->boolean(),
                'delivery_fee' => $this->faker->numberBetween(30, 150),
                'min_order_amount' => $this->faker->numberBetween(100, 500),
                'points_conversion' => $this->faker->numberBetween(1, 10),
                'tax_rate' => 12,
            ],
            'payment_methods' => ['cash', 'card', 'gcash'],
            'delivery_zones' => $this->faker->optional()->randomElement([
                ['Barangay 1', 'Barangay 2', 'Barangay 3'],
                ['Zone A', 'Zone B'],
                null,
            ]),
            'business_hours' => [
                'monday' => ['open' => '08:00', 'close' => '20:00'],
                'tuesday' => ['open' => '08:00', 'close' => '20:00'],
                'wednesday' => ['open' => '08:00', 'close' => '20:00'],
                'thursday' => ['open' => '08:00', 'close' => '20:00'],
                'friday' => ['open' => '08:00', 'close' => '20:00'],
                'saturday' => ['open' => '09:00', 'close' => '18:00'],
                'sunday' => ['open' => '09:00', 'close' => '17:00'],
            ],
            'rating' => $this->faker->randomFloat(1, 3, 5),
            'total_reviews' => $this->faker->numberBetween(0, 100),
            'total_orders' => $this->faker->numberBetween(0, 1000),
            'total_revenue' => $this->faker->randomFloat(2, 1000, 100000),
            'joined_at' => $this->faker->dateTimeBetween('-2 years'),
            'latitude' => $this->faker->latitude(14.5, 14.7),
            'longitude' => $this->faker->longitude(120.9, 121.1),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the merchant is verified
     */
    public function verified()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_verified' => true,
                'is_approved' => true,
            ];
        });
    }

    /**
     * Indicate that the merchant is active
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
                'is_approved' => true,
            ];
        });
    }

    /**
     * Indicate that the merchant has specific business type
     */
    public function businessType($type)
    {
        return $this->state(function (array $attributes) use ($type) {
            return [
                'business_type' => $type,
            ];
        });
    }

    /**
     * Indicate that the merchant has delivery
     */
    public function hasDelivery()
    {
        return $this->state(function (array $attributes) {
            return [
                'delivery_zones' => ['Barangay 1', 'Barangay 2', 'Barangay 3', 'Barangay 4', 'Barangay 5'],
            ];
        });
    }

    /**
     * Indicate that the merchant is approved
     */
    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_approved' => true,
            ];
        });
    }
}