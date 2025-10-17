<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Get 20% Off Your First Service',
                'description' => 'New customers receive 20% discount on their first booking',
                'image_url' => 'https://via.placeholder.com/800x400?text=20%+Off+First+Service',
                'link_type' => 'screen',
                'link_value' => 'services',
                'button_text' => 'Book Now',
                'start_date' => now(),
                'end_date' => now()->addDays(30),
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Spring Cleaning Special',
                'description' => 'Deep cleaning services at discounted rates',
                'image_url' => 'https://via.placeholder.com/800x400?text=Spring+Cleaning+Special',
                'link_type' => 'service',
                'link_value' => '1', // Deep House Cleaning service ID
                'button_text' => 'Learn More',
                'start_date' => now(),
                'end_date' => now()->addDays(60),
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Plumbing Emergency? We Got You!',
                'description' => '24/7 emergency plumbing services available',
                'image_url' => 'https://via.placeholder.com/800x400?text=Emergency+Plumbing',
                'link_type' => 'category',
                'link_value' => '2', // Plumbing category ID
                'button_text' => 'Call Now',
                'start_date' => now(),
                'end_date' => null,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Refer a Friend & Earn $25',
                'description' => 'Both you and your friend get $25 credit',
                'image_url' => 'https://via.placeholder.com/800x400?text=Referral+Program',
                'link_type' => 'url',
                'link_value' => 'https://proserve.com/referral',
                'button_text' => 'Refer Now',
                'start_date' => now(),
                'end_date' => null,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }
}
