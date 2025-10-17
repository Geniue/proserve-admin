<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\User;
use App\Models\ServiceBooking;
use App\Models\Banner;

class CheckData extends Command
{
    protected $signature = 'data:check';
    protected $description = 'Check database for imported data';

    public function handle()
    {
        $this->info('📊 Checking database for data...');
        $this->newLine();

        $categories = ServiceCategory::count();
        $this->line("Service Categories: {$categories}");
        
        if ($categories > 0) {
            $this->line("  Recent:");
            ServiceCategory::latest()->limit(3)->get()->each(function ($cat) {
                $this->line("    - {$cat->name} (firebase_id: " . ($cat->firebase_id ?? 'none') . ")");
            });
        }

        $this->newLine();
        $services = Service::count();
        $this->line("Services: {$services}");
        
        $this->newLine();
        $users = User::whereNotNull('firebase_uid')->count();
        $this->line("App Users (with firebase_uid): {$users}");
        
        $this->newLine();
        $bookings = ServiceBooking::count();
        $this->line("Bookings: {$bookings}");
        
        $this->newLine();
        $banners = Banner::count();
        $this->line("Banners: {$banners}");

        $this->newLine();
        
        if ($categories + $services + $users + $bookings + $banners == 0) {
            $this->warn('❌ No data found in database');
            $this->newLine();
            $this->info('💡 Options:');
            $this->line('   1. Import from Firestore: php artisan firebase:import');
            $this->line('   2. Create data in admin panel: http://127.0.0.1:8000/admin');
            $this->line('   3. Check if Firestore has data in Firebase Console');
        } else {
            $this->info('✅ Database has data!');
        }

        return Command::SUCCESS;
    }
}
