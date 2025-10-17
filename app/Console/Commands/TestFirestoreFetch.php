<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Firestore;

class TestFirestoreFetch extends Command
{
    protected $signature = 'firebase:test-fetch';
    protected $description = 'Test fetching documents from Firestore';

    public function handle()
    {
        $this->info('Testing Firestore document fetch...');
        $this->newLine();

        try {
            $firestore = app(Firestore::class);
            $this->line('✓ Got Firestore instance');

            $db = $firestore->database();
            $this->line('✓ Got database reference');

            $collection = $db->collection('users');
            $this->line('✓ Got collection reference');

            $this->line('Attempting to fetch documents...');
            
            // Try to get documents with a limit
            $query = $collection->limit(1);
            $this->line('✓ Created query with limit');
            
            try {
                $documents = $query->documents();
                $this->line('✓ Called documents() method');
            } catch (\Google\Cloud\Core\Exception\ServiceException $e) {
                $this->error('Google Cloud Service Exception!');
                $this->line('Status: ' . $e->getCode());
                $this->line('Message: ' . $e->getMessage());
                throw $e;
            } catch (\Exception $e) {
                $this->error('Exception during documents() call!');
                $this->line('Type: ' . get_class($e));
                $this->line('Message: ' . $e->getMessage());
                throw $e;
            }
            
            $count = 0;
            foreach ($documents as $document) {
                $count++;
                $this->info("Document ID: " . $document->id());
                
                if ($document->exists()) {
                    $data = $document->data();
                    $this->line('Data keys: ' . implode(', ', array_keys($data)));
                    
                    // Show first few fields
                    $email = $data['email'] ?? 'N/A';
                    $firstName = $data['firstName'] ?? $data['first_name'] ?? 'N/A';
                    $userType = $data['userType'] ?? 'N/A';
                    
                    $this->line("  Email: {$email}");
                    $this->line("  First Name: {$firstName}");
                    $this->line("  User Type: {$userType}");
                }
            }

            $this->newLine();
            $this->info("Successfully fetched {$count} document(s)!");
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to fetch documents!');
            $this->newLine();
            $this->error('Error: ' . $e->getMessage());
            $this->line('Class: ' . get_class($e));
            $this->line('File: ' . $e->getFile() . ':' . $e->getLine());
            $this->newLine();
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());
            
            return Command::FAILURE;
        }
    }
}
