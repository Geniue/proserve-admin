<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call([
        //     RoleSeeder::class,
        //     ServiceCategorySeeder::class,
        //     ServiceSeeder::class,
        //     BannerSeeder::class,
        //     AppConfigSeeder::class,
        // ]);

        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}
