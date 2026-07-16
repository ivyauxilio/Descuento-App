<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = [
            [
                'name' => 'Metro Manila',
                'region' => 'National Capital Region (NCR)'
            ],
            [
                'name' => 'Cebu',
                'region' => 'Central Visayas (Region VII)'
            ],
            [
                'name' => 'Davao del Sur',
                'region' => 'Davao Region (Region XI)'
            ],
            [
                'name' => 'Laguna',
                'region' => 'Calabarzon (Region IV-A)'
            ],
            [
                'name' => 'Pampanga',
                'region' => 'Central Luzon (Region III)'
            ],
        ];

        foreach ($provinces as $province) {
            Province::create([
                'province_id' => Str::uuid(),
                'name' => $province['name'],
                'region' => $province['region'],
            ]);
        }

        $this->command->info('5 provinces seeded successfully!');
    }
}