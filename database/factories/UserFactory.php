<?php
// database/factories/UserFactory.php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();

        return [
            'uuid' => (string) Str::uuid(),
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'email_verified_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now'),
            'password' => Hash::make('password123'),
            'role' => $this->faker->randomElement(['customer', 'merchant', 'admin']),
            'status' => $this->faker->randomElement(['active', 'inactive', 'suspended']),
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the user is a merchant
     */
    public function merchant()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'merchant',
                'status' => 'active',
            ];
        });
    }

    /**
     * Indicate that the user is an admin
     */
    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'admin',
                'status' => 'active',
            ];
        });
    }

    /**
     * Indicate that the user is a customer
     */
    public function customer()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'customer',
                'status' => 'active',
            ];
        });
    }

    /**
     * Indicate that the user is verified
     */
    public function verified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the user is active
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
            ];
        });
    }

    /**
     * Indicate that the user is inactive
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'inactive',
            ];
        });
    }

    /**
     * Indicate that the user is suspended
     */
    public function suspended()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'suspended',
            ];
        });
    }

    /**
     * Indicate that the user has a specific role
     */
    public function withRole($role)
    {
        return $this->state(function (array $attributes) use ($role) {
            return [
                'role' => $role,
            ];
        });
    }
}