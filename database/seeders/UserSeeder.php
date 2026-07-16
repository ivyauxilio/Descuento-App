<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'firstname' => 'Super',
            'lastname' => 'Admin',
            'email' => 'admin@example.com',
            'phone' => '+1234567890',
            'email_verified_at' => now(),
            'password' => Hash::make('Admin@123456'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create merchant user
        User::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'firstname' => 'Jane',
            'lastname' => 'Merchant',
            'email' => 'merchant@example.com',
            'phone' => '+1234567891',
            'email_verified_at' => now(),
            'password' => Hash::make('Merchant@123456'),
            'role' => 'merchant',
            'status' => 'active',
        ]);

        // Create customer user
        User::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'firstname' => 'Bob',
            'lastname' => 'Customer',
            'email' => 'customer@example.com',
            'phone' => '+1234567892',
            'email_verified_at' => now(),
            'password' => Hash::make('Customer@123456'),
            'role' => 'customer',
            'status' => 'active',
        ]);

        // Create 50 random users using factory
        // User::factory()->count(50)->create();
    }
}