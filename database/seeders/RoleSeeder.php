<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $admin = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Full system access',
        ]);

        $serviceProvider = Role::create([
            'name' => 'service_provider',
            'display_name' => 'Service Provider',
            'description' => 'Can provide services and manage bookings',
        ]);

        $customer = Role::create([
            'name' => 'customer',
            'display_name' => 'Customer',
            'description' => 'Can book services',
        ]);

        // Create permissions
        $permissions = [
            ['name' => 'view_dashboard', 'display_name' => 'View Dashboard', 'description' => 'Access admin dashboard'],
            ['name' => 'manage_users', 'display_name' => 'Manage Users', 'description' => 'Create, edit, delete users'],
            ['name' => 'manage_services', 'display_name' => 'Manage Services', 'description' => 'Manage service catalog'],
            ['name' => 'manage_bookings', 'display_name' => 'Manage Bookings', 'description' => 'View and manage all bookings'],
            ['name' => 'manage_content', 'display_name' => 'Manage Content', 'description' => 'Manage pages, FAQs, banners'],
            ['name' => 'manage_settings', 'display_name' => 'Manage Settings', 'description' => 'Configure app settings'],
            ['name' => 'view_own_bookings', 'display_name' => 'View Own Bookings', 'description' => 'View own bookings'],
            ['name' => 'create_booking', 'display_name' => 'Create Booking', 'description' => 'Book services'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::create($permissionData);
        }

        // Assign permissions to roles
        $adminPermissions = Permission::all();
        $admin->permissions()->attach($adminPermissions);

        $providerPermissions = Permission::whereIn('name', [
            'view_dashboard',
            'view_own_bookings',
            'manage_bookings',
        ])->get();
        $serviceProvider->permissions()->attach($providerPermissions);

        $customerPermissions = Permission::whereIn('name', [
            'view_own_bookings',
            'create_booking',
        ])->get();
        $customer->permissions()->attach($customerPermissions);
    }
}
