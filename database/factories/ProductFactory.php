<?php
// database/factories/ProductFactory.php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $categories = ['Fruits', 'Vegetables', 'Meat', 'Poultry', 'Seafood', 'Dairy', 'Bakery', 'Beverages', 'Snacks', 'Frozen', 'Canned', 'Dry Goods', 'Household', 'Personal Care', 'Baby', 'Pet'];
        $units = ['piece', 'kg', 'gram', 'liter', 'ml', 'dozen', 'box', 'pack', 'bottle', 'can'];

        $name = $this->faker->words(3, true);
        $price = $this->faker->randomFloat(2, 10, 1000);

        return [
            'merchant_id' => Merchant::factory(),
            'name' => ucwords($name),
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . \Illuminate\Support\Str::random(6),
            'description' => $this->faker->paragraphs(2, true),
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-###-???')),
            'barcode' => $this->faker->optional()->ean13(),
            'price' => $price,
            'original_price' => $this->faker->optional()->randomFloat(2, $price + 10, $price + 200),
            'category' => $this->faker->randomElement($categories),
            'sub_category' => $this->faker->optional()->word(),
            'brand' => $this->faker->optional()->company(),
            'unit' => $this->faker->randomElement($units),
            'unit_value' => $this->faker->optional()->randomFloat(2, 1, 100),
            'stock_quantity' => $this->faker->numberBetween(0, 1000),
            'min_stock_alert' => $this->faker->numberBetween(5, 20),
            'in_stock' => $this->faker->boolean(80),
            'is_featured' => $this->faker->boolean(20),
            'is_active' => $this->faker->boolean(90),
            'image_url' => $this->faker->optional(0.7)->imageUrl(400, 400, 'food'),
            'images' => $this->faker->optional()->randomElement([
                [null, null, null],
                [$this->faker->imageUrl(400, 400, 'food'), $this->faker->imageUrl(400, 400, 'food')],
                null,
            ]),
            'attributes' => $this->faker->optional()->randomElement([
                ['size' => 'Large', 'color' => 'Red'],
                ['size' => 'Medium', 'flavor' => 'Original'],
                ['size' => 'Small', 'package' => 'Box'],
                null,
            ]),
            'weight' => $this->faker->optional()->randomElement(['500g', '1kg', '2kg', '250g', '750ml']),
            'nutritional_info' => $this->faker->optional()->randomElement([
                'Calories: 100kcal, Protein: 5g, Carbs: 20g',
                'Calories: 50kcal, Protein: 2g, Carbs: 10g',
                'Calories: 200kcal, Protein: 10g, Carbs: 30g',
                null,
            ]),
            'country_of_origin' => $this->faker->optional()->randomElement(['Philippines', 'USA', 'China', 'Japan', 'Korea', 'Thailand', 'Vietnam']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the product is active
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
                'in_stock' => true,
                'stock_quantity' => $this->faker->numberBetween(10, 500),
            ];
        });
    }

    /**
     * Indicate that the product is featured
     */
    public function featured()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_featured' => true,
            ];
        });
    }

    /**
     * Indicate that the product is on sale
     */
    public function onSale()
    {
        return $this->state(function (array $attributes) {
            $price = $this->faker->randomFloat(2, 50, 500);
            return [
                'price' => $price,
                'original_price' => $this->faker->randomFloat(2, $price + 20, $price + 100),
            ];
        });
    }

    /**
     * Indicate that the product is low stock
     */
    public function lowStock()
    {
        return $this->state(function (array $attributes) {
            $stock = $this->faker->numberBetween(1, 5);
            return [
                'stock_quantity' => $stock,
                'min_stock_alert' => $this->faker->numberBetween(5, 10),
                'in_stock' => true,
            ];
        });
    }

    /**
     * Indicate that the product is out of stock
     */
    public function outOfStock()
    {
        return $this->state(function (array $attributes) {
            return [
                'stock_quantity' => 0,
                'in_stock' => false,
            ];
        });
    }
}