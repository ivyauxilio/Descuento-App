<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Food & Beverage
            'Restaurant' => 'Full-service restaurants offering dine-in, takeout, and delivery',
            'Fast Food' => 'Quick-service restaurants with fast food and takeaway options',
            'Cafe & Coffee Shop' => 'Coffee shops, cafes, and tea houses',
            'Bakery & Pastry' => 'Bakeries, pastry shops, and dessert establishments',
            'Food Truck' => 'Mobile food vendors and food trucks',
            'Bar & Pub' => 'Bars, pubs, and nightlife establishments',
            
            // Retail
            'Retail Store' => 'General retail stores selling various products',
            'Clothing & Fashion' => 'Apparel, fashion, and accessory stores',
            'Electronics Store' => 'Electronics, gadgets, and technology stores',
            'Grocery & Supermarket' => 'Grocery stores, supermarkets, and convenience stores',
            'Pharmacy & Drugstore' => 'Pharmacies, drugstores, and health product retailers',
            'Bookstore & Stationery' => 'Bookstores, stationery, and office supply stores',
            'Furniture & Home Decor' => 'Furniture stores, home decor, and interior design shops',
            'Hardware & DIY' => 'Hardware stores, DIY, and home improvement centers',
            'Sports & Outdoors' => 'Sporting goods, outdoor equipment, and fitness stores',
            'Pet Store' => 'Pet shops, pet supplies, and animal care stores',
            
            // Services
            'Beauty Salon' => 'Hair salons, beauty parlors, and grooming services',
            'Spa & Wellness' => 'Spas, wellness centers, and massage therapy',
            'Fitness & Gym' => 'Gyms, fitness centers, and health clubs',
            'Dental Clinic' => 'Dental practices and oral care services',
            'Medical Clinic' => 'Medical clinics, healthcare centers, and doctor practices',
            'Veterinary Clinic' => 'Veterinary services and animal healthcare',
            'Legal Services' => 'Law firms, legal services, and attorney practices',
            'Accounting & Tax' => 'Accounting firms, tax services, and financial advisory',
            'Real Estate Agency' => 'Real estate brokers, agents, and property management',
            'Travel Agency' => 'Travel agencies, tour operators, and booking services',
            'Hotel & Accommodation' => 'Hotels, motels, resorts, and accommodation providers',
            'Event Planning' => 'Event planning, party planning, and wedding services',
            'Photography Studio' => 'Photography studios, event photographers, and videographers',
            'Cleaning Services' => 'Cleaning, janitorial, and sanitation services',
            'Laundry & Dry Cleaning' => 'Laundry services, dry cleaners, and fabric care',
            'Auto Repair & Maintenance' => 'Auto repair shops, mechanics, and vehicle maintenance',
            'Car Wash' => 'Car wash, detailing, and auto cleaning services',
            'Tutoring & Education' => 'Tutoring centers, educational services, and training',
            
            // Arts & Entertainment
            'Art Gallery' => 'Art galleries, exhibition spaces, and art dealers',
            'Movie Theater' => 'Cinemas, movie theaters, and film screening venues',
            'Music Venue' => 'Concert halls, music venues, and performance spaces',
            'Arcade & Gaming' => 'Arcades, gaming centers, and entertainment venues',
            'Museum' => 'Museums, historical sites, and cultural institutions',
            
            // Digital & Tech
            'E-commerce Store' => 'Online retail stores and e-commerce platforms',
            'Digital Agency' => 'Digital marketing, web development, and creative agencies',
            'IT Services' => 'IT support, software development, and technology services',
            'Telecommunications' => 'Telecom providers, internet, and mobile services',
            
            // Miscellaneous
            'Florist' => 'Flower shops, florists, and plant stores',
            'Jewelry Store' => 'Jewelry shops, goldsmiths, and accessories',
            'Optical Store' => 'Optical shops, eyewear, and vision care',
            'Insurance Agency' => 'Insurance brokers, agents, and financial protection',
            'Non-Profit Organization' => 'Charities, NGOs, and community organizations',
            'Freelancer' => 'Independent professionals and freelance services',
            'Other' => 'Other types of businesses not listed in categories',
        ];

        foreach ($categories as $name => $description) {
            Category::create([
                'category_id' => Str::uuid(),
                'name' => $name,
                'description' => $description,
            ]);
        }

        $this->command->info('Categories seeded successfully!');
    }
}