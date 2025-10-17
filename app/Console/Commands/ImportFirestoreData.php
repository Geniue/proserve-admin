<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ImportAllFirestoreData;

class ImportFirestoreData extends Command
{
    protected $signature = 'firebase:import';
    protected $description = 'Import all data from Firestore into PostgreSQL';

    public function handle()
    {
        $this->info('🔥 Starting Firebase data import...');
        $this->newLine();

        // Check if sync is enabled
        $syncEnabled = config('firebase.sync_enabled', false);
        $this->line("Firebase Sync Enabled: " . ($syncEnabled ? 'Yes' : 'No'));
        
        if (!$syncEnabled) {
            $this->warn('⚠️  Firebase sync is disabled in config!');
            $this->line('Set FIREBASE_SYNC_ENABLED=true in .env and run: php artisan config:clear');
            return Command::FAILURE;
        }

        $this->newLine();
        $this->line('Starting import from Firestore...');

        try {
            $this->line('Creating job instance...');
            $job = new ImportAllFirestoreData();
            
            $this->line('Executing import...');
            $job->handle();

            $this->newLine();
            $this->info('✅ Import completed successfully!');
            $this->newLine();

            // Show what was imported
            $this->info('📊 Imported data summary:');
            
            $categories = \App\Models\ServiceCategory::count();
            $this->line("   - Service Categories: {$categories}");
            
            $services = \App\Models\Service::count();
            $this->line("   - Services: {$services}");
            
            $users = \App\Models\User::whereNotNull('firebase_uid')->count();
            $this->line("   - App Users: {$users}");
            
            $bookings = \App\Models\ServiceBooking::count();
            $this->line("   - Bookings: {$bookings}");
            
            $banners = \App\Models\Banner::count();
            $this->line("   - Banners: {$banners}");

            $this->newLine();
            $this->info('🎉 You can now view the data in your admin panel!');
            $this->line('   👉 http://127.0.0.1:8000/admin');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Import failed!');
            $this->newLine();
            $this->error('Error: ' . $e->getMessage());
            $this->line('File: ' . $e->getFile() . ':' . $e->getLine());
            $this->newLine();
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());
            
            return Command::FAILURE;
        }
    }
}
