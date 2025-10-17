<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Home Cleaning',
                'slug' => 'home-cleaning',
                'description' => 'Professional home cleaning services',
                'icon_url' => 'https://via.placeholder.com/100?text=Clean',
                'color_code' => '#3B82F6',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Plumbing',
                'slug' => 'plumbing',
                'description' => 'Expert plumbing repairs and installations',
                'icon_url' => 'https://via.placeholder.com/100?text=Plumb',
                'color_code' => '#10B981',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Electrical',
                'slug' => 'electrical',
                'description' => 'Licensed electrical services',
                'icon_url' => 'https://via.placeholder.com/100?text=Electric',
                'color_code' => '#F59E0B',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'HVAC',
                'slug' => 'hvac',
                'description' => 'Heating, ventilation, and air conditioning',
                'icon_url' => 'https://via.placeholder.com/100?text=HVAC',
                'color_code' => '#EF4444',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Pest Control',
                'slug' => 'pest-control',
                'description' => 'Professional pest elimination services',
                'icon_url' => 'https://via.placeholder.com/100?text=Pest',
                'color_code' => '#8B5CF6',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Carpentry',
                'slug' => 'carpentry',
                'description' => 'Custom woodworking and repairs',
                'icon_url' => 'https://via.placeholder.com/100?text=Carpentry',
                'color_code' => '#D97706',
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ServiceCategory::create($category);
        }
    }
}
