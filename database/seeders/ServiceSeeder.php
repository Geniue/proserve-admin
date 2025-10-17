<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $homeCleaning = ServiceCategory::where('slug', 'home-cleaning')->first();
        $plumbing = ServiceCategory::where('slug', 'plumbing')->first();
        $electrical = ServiceCategory::where('slug', 'electrical')->first();

        $services = [
            [
                'category_id' => $homeCleaning->id,
                'name' => 'Deep House Cleaning',
                'slug' => 'deep-house-cleaning',
                'description' => 'Comprehensive deep cleaning service for your entire home including kitchen, bathrooms, bedrooms, and living areas.',
                'short_description' => 'Complete home deep cleaning',
                'icon_url' => 'https://via.placeholder.com/150?text=Deep+Clean',
                'images' => json_encode(['https://via.placeholder.com/400x300?text=Service+1']),
                'price_min' => 80.00,
                'price_max' => 200.00,
                'price_unit' => 'per service',
                'duration' => 180,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $homeCleaning->id,
                'name' => 'Regular Home Cleaning',
                'slug' => 'regular-home-cleaning',
                'description' => 'Standard cleaning service for maintaining your home on a regular basis.',
                'short_description' => 'Weekly/bi-weekly cleaning',
                'icon_url' => 'https://via.placeholder.com/150?text=Regular+Clean',
                'images' => json_encode(['https://via.placeholder.com/400x300?text=Service+2']),
                'price_min' => 50.00,
                'price_max' => 120.00,
                'price_unit' => 'per service',
                'duration' => 120,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
            ],
            [
                'category_id' => $plumbing->id,
                'name' => 'Drain Cleaning',
                'slug' => 'drain-cleaning',
                'description' => 'Professional drain cleaning and unclogging services for sinks, toilets, and pipes.',
                'short_description' => 'Quick drain unclogging',
                'icon_url' => 'https://via.placeholder.com/150?text=Drain',
                'images' => json_encode(['https://via.placeholder.com/400x300?text=Plumbing+1']),
                'price_min' => 75.00,
                'price_max' => 150.00,
                'price_unit' => 'per job',
                'duration' => 90,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'category_id' => $plumbing->id,
                'name' => 'Pipe Repair & Installation',
                'slug' => 'pipe-repair-installation',
                'description' => 'Expert pipe repair, replacement, and new installations for residential and commercial properties.',
                'short_description' => 'Pipe fixes and new installations',
                'icon_url' => 'https://via.placeholder.com/150?text=Pipes',
                'images' => json_encode(['https://via.placeholder.com/400x300?text=Plumbing+2']),
                'price_min' => 100.00,
                'price_max' => 300.00,
                'price_unit' => 'per job',
                'duration' => 120,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
            ],
            [
                'category_id' => $electrical->id,
                'name' => 'Electrical Wiring',
                'slug' => 'electrical-wiring',
                'description' => 'Professional electrical wiring services for new installations and rewiring projects.',
                'short_description' => 'Safe electrical wiring',
                'icon_url' => 'https://via.placeholder.com/150?text=Wiring',
                'images' => json_encode(['https://via.placeholder.com/400x300?text=Electric+1']),
                'price_min' => 120.00,
                'price_max' => 400.00,
                'price_unit' => 'per job',
                'duration' => 180,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
