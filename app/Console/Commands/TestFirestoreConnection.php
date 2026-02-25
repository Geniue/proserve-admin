<?php

namespace App\Console\Commands;

use App\Services\FirestoreRestClient;
use Illuminate\Console\Command;

class TestFirestoreConnection extends Command
{
    protected $signature = 'firebase:test {--write : Test write operation} {--collection=users : Collection to test read from}';
    protected $description = 'Test Firebase Firestore connection';

    public function handle()
    {
        $this->info('Testing Firestore Connection...');
        $this->newLine();

        // Check configuration
        $projectId = env('FIREBASE_PROJECT_ID');
        $credentialsPath = env('FIREBASE_CREDENTIALS');
        
        $this->line("Project ID: " . ($projectId ?: '<not set>'));
        $this->line("Credentials Path: " . ($credentialsPath ?: '<not set>'));
        
        if ($credentialsPath) {
            $fullPath = base_path($credentialsPath);
            $exists = file_exists($fullPath);
            $this->line("Credentials File Exists: " . ($exists ? '✓ Yes' : '✗ No'));
            
            if (!$exists) {
                $this->newLine();
                $this->error('Firebase credentials file not found!');
                $this->newLine();
                $this->warn('To fix this, you need to:');
                $this->line('1. Go to Firebase Console: https://console.firebase.google.com/project/' . ($projectId ?: 'YOUR_PROJECT') . '/settings/serviceaccounts/adminsdk');
                $this->line('2. Click "Generate new private key"');
                $this->line('3. Save the downloaded JSON file as: storage/firebase-credentials.json');
                $this->newLine();
                return 1;
            }
        }

        $this->newLine();
        
        try {
            $client = new FirestoreRestClient();
            $collection = $this->option('collection');
            
            // Test read operation
            $this->info("Testing READ operation (fetching {$collection} collection)...");
            [$documents, $nextToken] = $client->listDocuments($collection, 10);
            $this->info('✓ Read successful! Found ' . count($documents) . ' document(s)');
            
            // Show document IDs
            foreach ($documents as $doc) {
                $name = $doc['name'] ?? '';
                $id = basename($name);
                $this->line("  - {$id}");
            }
            
            if ($this->option('write')) {
                // Test write operation
                $this->newLine();
                $this->info('Testing WRITE operation...');
                
                $testData = [
                    'testField' => 'Test from Laravel Admin ' . now()->toIso8601String(),
                    'timestamp' => now()->toIso8601String(),
                ];
                
                $result = $client->setDocument('_admin_test', 'connection_test', $testData, true);
                $this->info('✓ Write successful!');
                
                // Clean up test document
                $client->deleteDocument('_admin_test', 'connection_test');
                $this->info('✓ Delete successful (cleaned up test document)');
            }
            
            $this->newLine();
            $this->info('🎉 Firestore connection is working!');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('Connection failed: ' . $e->getMessage());
            
            if (str_contains($e->getMessage(), 'credentials')) {
                $this->newLine();
                $this->warn('The credentials file is missing or invalid.');
                $this->line('Download it from: https://console.firebase.google.com/project/' . ($projectId ?: 'YOUR_PROJECT') . '/settings/serviceaccounts/adminsdk');
            }
            
            return 1;
        }
    }
}
