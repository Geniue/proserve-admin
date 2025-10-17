<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Firestore;
use Exception;

class TestFirebaseConnection extends Command
{
    protected $signature = 'firebase:test-connection';
    protected $description = 'Test Firebase Firestore connection';

    public function handle()
    {
        $this->info('🔥 Testing Firebase Connection...');
        $this->newLine();

        try {
            // Test Firestore connection
            $firestore = app(Firestore::class);
            $db = $firestore->database();
            
            $this->info('✅ Connected to Firebase Firestore!');
            $this->newLine();

            // Test reading from serviceCategories collection
            $this->info('📦 Testing Firestore access:');
            
            $testCollection = $db->collection('serviceCategories');
            $documents = $testCollection->limit(5)->documents();
            
            $count = 0;
            foreach ($documents as $document) {
                if ($document->exists()) {
                    $data = $document->data();
                    $this->line("   - {$document->id()}: " . ($data['name'] ?? 'Unnamed'));
                    $count++;
                }
            }
            
            if ($count === 0) {
                $this->warn("   ℹ 'serviceCategories' collection is empty or doesn't exist yet");
            } else {
                $this->info("   ✅ Found {$count} service categories");
            }

            $this->newLine();
            $this->info('🎉 Firebase connection test completed successfully!');
            $this->newLine();
            
            $this->info('📝 Next steps:');
            $this->line('   1. Import data: php artisan tinker');
            $this->line('   2. Run: (new \\App\\Jobs\\ImportAllFirestoreData)->handle();');
            $this->line('   3. Or dispatch: \\App\\Jobs\\ImportAllFirestoreData::dispatch();');

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('❌ Firebase connection failed!');
            $this->newLine();
            $this->error('Error: ' . $e->getMessage());
            $this->error('Type: ' . get_class($e));
            $this->newLine();
            
            $this->warn('💡 Troubleshooting:');
            $this->line('   1. Check if storage/firebase-credentials.json exists');
            $this->line('   2. Verify the JSON file is valid');
            $this->line('   3. Check if service account has Firestore permissions');
            $this->line('   4. Run: php artisan config:clear');
            
            return Command::FAILURE;
        }
    }
}
